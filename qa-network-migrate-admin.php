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
							qa_post_unindex($gchild['postid']);
							qa_db_post_delete($gchild['postid']); // also deletes any related voteds due to cascading
						}
						mysql_free_result($query2);
					}
					qa_post_unindex($child['postid']);
					qa_db_post_delete($child['postid']); // also deletes any related voteds due to cascading
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

				qa_post_unindex($post['postid']);
				qa_db_post_delete($post['postid']); // also deletes any related voteds due to cascading

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
		
		function post_migrate($prefix,$post,$parent=null,$cat=null) {
			require_once QA_INCLUDE_DIR.'qa-app-post-update.php';
			
			// get new parent id
			
			$result = mysql_query("SHOW TABLE STATUS LIKE '".$prefix."posts'");
			$row = mysql_fetch_array($result);
			$nid = $row['Auto_increment'];

			// copy post to new site
			
			qa_db_query_sub(
				'INSERT INTO '.$prefix.'posts (type,parentid,categoryid,catidpath1,catidpath2,catidpath3,acount,amaxvote,selchildid,closedbyid,userid,cookieid,createip,lastuserid,lastip,upvotes,downvotes,netvotes,lastviewip,views,hotness,flagcount,format,created,updated,updatetype,title,content,tags,notify) VALUES($,'.($parent?qa_db_escape_string($parent):'NULL').','.($cat?qa_db_escape_string($cat):'NULL').',NULL,NULL,NULL,#,#,#,#,#,#,#,#,#,#,#,#,#,#,#,#,$,#,#,$,$,$,$,$)',
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
			
			return $nid;
		}
    }