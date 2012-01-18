<?php
    class qa_network_migrate_admin {

		function allow_template($template)
		{
			return ($template!='admin');
		}

		function option_default($option) {
			switch($option) {
				case 'network_site_migrated_text':
					return true;
				default:
					return null;
			}
		}

		function admin_form(&$qa_content)
		{
			require_once QA_INCLUDE_DIR.'qa-app-posts.php';
			qa_db_query_sub(
				'CREATE TABLE IF NOT EXISTS ^postmeta (
				meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				post_id bigint(20) unsigned NOT NULL,
				meta_key varchar(255) DEFAULT \'\',
				meta_value longtext,
				PRIMARY KEY (meta_id),
				KEY post_id (post_id),
				KEY meta_key (meta_key)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8'
			);	
			$ok = null;
			

			// get sites

			$idx = 0;
			while(qa_opt('network_site_'.$idx.'_url')) {
				$sites[qa_opt('network_site_'.$idx.'_prefix')] = qa_opt('network_site_'.$idx++.'_title');
			}
		
			if (qa_clicked('network_site_migrate')) {
				
				$pid = qa_post_text('network_site_migrate_id');
				$prefix = qa_db_escape_string(qa_post_text('network_site_migrate_site'));
				$cat = qa_post_text('network_site_migrate_cat');
				
				// migrate question to get new id

				$post = qa_db_read_one_assoc(
					qa_db_query_sub(
						'SELECT * FROM ^posts WHERE postid=#',
						$pid
					),
					true
				);				
				$nid = $this->post_migrate($prefix,$post,null,$cat);

				// migrate children

				$query = qa_db_query_sub(
					'SELECT * FROM ^posts WHERE parentid=#',
					$pid
				);
				
				$children = 0;
				
				while(($child = qa_db_read_one_assoc($query,true)) !== null) {

					// migrate child (comment or answer to question)
					
					$ncid = $this->post_migrate($prefix,$child,$nid);
					$children++;
					
					if(strpos($child['type'],'A') === 0) {
						// update selchildid if selected
						
						if($child['postid'] == $post['selchildid']) {
							qa_db_query_sub(
								'UPDATE '.$prefix.'posts SET selchildid=# WHERE postid=#',
								$ncid,$nid
							);					
						}

						// check for grandchildren
						
						$query2 = qa_db_query_sub (
							'SELECT * FROM ^posts WHERE parentid=#',
							$child['postid']
						);
						while(($gchild = qa_db_read_one_assoc($query2,true)) !== null) {
							
							// unrelate related questions... any other choice?
							if(strpos($gchild['type'],'Q') === 0) {
								qa_db_query_sub(
									'UPDATE ^posts SET parentid=NULL WHERE postid=#',
									$gchild['postid']
								);
							}
							else { // migrate comments to answers
								$this->post_migrate($prefix,$gchild,$ncid);
								$children++;
							}
							qa_post_delete($gchild['postid']);
						}
						mysql_free_result($query2);
					}
					qa_post_delete($child['postid']);
				}
				mysql_free_result($query);
				
				// flag as migrated
				qa_db_query_sub(
					'CREATE TABLE IF NOT EXISTS '.$prefix.'postmeta (
					meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					post_id bigint(20) unsigned NOT NULL,
					meta_key varchar(255) DEFAULT \'\',
					meta_value longtext,
					PRIMARY KEY (meta_id),
					KEY post_id (post_id),
					KEY meta_key (meta_key)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8'
				);					
				qa_db_query_sub(
					'INSERT INTO '.$prefix.'postmeta (post_id,meta_key,meta_value) VALUES (#,$,$)',
					$nid,'migrated',QA_MYSQL_TABLE_PREFIX.'|'.time().'|'.qa_get_logged_in_handle()
				);
				
				qa_post_delete($post['postid']);

				$ok = 'Post '.$pid.($children?' and '.$children.' child posts':'').' migrated to '.$sites[qa_post_text('network_site_migrate_site')].'.';
			}

		// Create the form for display

			$fields = array();

			$fields[] = array(
				'label' => 'Post ID to migrate',
				'tags' => 'NAME="network_site_migrate_id"',
				'note' => 'Warning: will migrate all child posts as well.',
				'type' => 'number',
			);

			$fields[] = array(
				'label' => 'Migrate to site:',
				'tags' => 'NAME="network_site_migrate_site"',
				'type' => 'select',
				'options' => $sites,
			);	

			$fields[] = array(
				'label' => 'Category ID on new site',
				'tags' => 'NAME="network_site_migrate_cat"',
				'note' => 'Optional - cat ID must exist on new site',
				'type' => 'number',
			);
						
			return array(           
				'ok' => ($ok && !isset($error)) ? $ok : null,
					
				'fields' => $fields,
				 
				'buttons' => array(
					array(
					'label' => 'Migrate Post',
					'tags' => 'NAME="network_site_migrate"',
					),
				),
			);
		}
		
		function post_migrate($prefix,$post,$parentid=null,$cat=null) {
			require_once QA_INCLUDE_DIR.'qa-app-post-update.php';
			
			// get new parent id
			
			$result = mysql_query("SHOW TABLE STATUS LIKE '".$prefix."posts'");
			$row = mysql_fetch_array($result);
			$nid = $row['Auto_increment'];

			// copy post to new site
			
			qa_db_query_sub(
				'INSERT INTO '.$prefix.'posts (type,parentid,categoryid,catidpath1,catidpath2,catidpath3,acount,amaxvote,selchildid,closedbyid,userid,cookieid,createip,lastuserid,lastip,upvotes,downvotes,netvotes,lastviewip,views,hotness,flagcount,format,created,updated,updatetype,title,content,tags,notify) VALUES($,'.($parentid?qa_db_escape_string($parentid):'NULL').','.($cat?qa_db_escape_string($cat):'NULL').',NULL,NULL,NULL,#,#,#,#,#,#,#,#,#,#,#,#,#,#,#,#,$,#,#,$,$,$,$,$)',
				$post['type'],$post['acount'],$post['amaxvote'],$post['selchildid'],$post['closedbyid'],$post['userid'],$post['cookieid'],$post['createip'],$post['lastuserid'],$post['lastip'],$post['upvotes'],$post['downvotes'],$post['netvotes'],$post['lastviewip'],$post['views'],$post['hotness'],$post['flagcount'],$post['format'],$post['created'],$post['updated'],$post['updatetype'],$post['title'],$post['content'],$post['tags'],$post['notify']
			);	

			mysql_free_result($result);

			// get old uservotes
			
			$query = qa_db_query_sub(
				'SELECT * FROM ^uservotes WHERE postid=#',
				$post['postid']
			);
			
			while(($vote = qa_db_read_one_assoc($query,true)) !== null) {
				// add new uservote
				qa_db_query_sub(
					'INSERT INTO '.$prefix.'uservotes (postid,userid,vote,flag) VALUES(#,#,#,#)',
					$nid,$vote['userid'],$vote['vote'],$vote['flag']
				);	
			}

			mysql_free_result($query);
			
			// make remote request for update
			
			$idx = 0;
			$url = '';
			while($idx <= (int)qa_opt('network_site_number')) {
				if(qa_opt('network_site_'.$idx.'_prefix') == $prefix) {
					$url = qa_opt('network_site_'.$idx.'_url');
					break;
				}
				$idx++;
			}
			
			// set migrate prefix to invoke override, changing the set of tables temporarily -- yikes!
			
			global $migrate_change_db;
			$migrate_change_db = $prefix;
			
			require_once QA_INCLUDE_DIR.'qa-db-post-create.php';
			require_once QA_INCLUDE_DIR.'qa-db-post-update.php';
			require_once QA_INCLUDE_DIR.'qa-db-points.php';
			require_once QA_INCLUDE_DIR.'qa-db-votes.php';
			
			$post = qa_db_read_one_assoc(
				qa_db_query_sub(
					'SELECT * FROM ^posts WHERE postid=#',
					$nid
				),
				true
			);

			qa_db_posts_calc_category_path($post['postid']);

			$text=qa_post_content_to_text($post['content'], $post['format']);			
			
			if($post['type'] == 'Q') { 
				$tagstring=qa_post_tags_to_tagstring($post['tags']);

				qa_db_category_path_qcount_update(qa_db_post_get_category_path($post['postid']));
				qa_db_hotness_update($post['postid']);
				qa_post_index($post['postid'], 'Q', $post['postid'], null, $post['title'], $post['content'], $post['format'], $text, $tagstring);
				
				qa_db_points_update_ifuser($post['userid'], array('qposts', 'aselects', 'qvoteds', 'upvoteds', 'downvoteds'));
				
				qa_db_qcount_update();
				qa_db_unaqcount_update();
				qa_db_unselqcount_update();
				qa_db_unupaqcount_update();
			}
			else if($post['type'] == 'A') { 
				$question = qa_db_read_one_assoc(
					qa_db_query_sub(
						'SELECT * FROM ^posts WHERE postid=#',
						$parentid
					),
					true
				);
				if ($question['type']=='Q')
					qa_post_index($post['postid'], 'A', $question['postid'], $question['postid'], null, $post['content'], $post['format'], $text, null);
					
				qa_db_post_acount_update($question['postid']);
				qa_db_hotness_update($question['postid']);
				qa_db_points_update_ifuser($post['userid'], array('aposts', 'aselecteds', 'avoteds', 'upvoteds', 'downvoteds'));
				qa_db_acount_update();
				qa_db_unaqcount_update();
				qa_db_unupaqcount_update();
			}
			else if($post['type'] == 'C') {
				$parent = qa_db_read_one_assoc(
					qa_db_query_sub(
						'SELECT * FROM ^posts WHERE postid=#',
						$parentid
					),
					true
				);
				if(strpos($parent['type'],'A') === 0)
					$question =  qa_db_read_one_assoc(
						qa_db_query_sub(
							'SELECT * FROM ^posts WHERE postid=#',
							$parent['postid']
						),
						true
					);
				else $question = $parent;
				
				if ( ($question['type']=='Q') && (($parent['type']=='Q') || ($parent['type']=='A')) ) // only index if antecedents fully visible
					qa_post_index($post['postid'], 'C', $question['postid'], $parent['postid'], null, $post['content'], $post['format'], $text, null);
					
				qa_db_points_update_ifuser($post['userid'], array('cposts'));
				qa_db_ccount_update();
			}
			
			$migrate_change_db = null;
			
			return $nid;
		}
    }