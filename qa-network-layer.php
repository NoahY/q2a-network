<?php

	class qa_html_theme_layer extends qa_html_theme_base {

	// theme replacement functions

		function head_custom() {
			$this->output('<style>',str_replace('^',QA_HTML_THEME_LAYER_URLTOROOT,qa_opt('network_site_css')),'</style>');
			if(isset($this->content['form_activity'])) {
				$this->content['form_activity']['fields']['answers'] = array(
					'type' => 'static',
					'label' => qa_lang_html('network/network_sites'),
					'value' => '<SPAN CLASS="qa-uf-user-network-sites">'.$this->network_user_sites($this->content['raw']['userid']).'</SPAN>',
				);
			}
			qa_html_theme_base::head_custom();
		}

		function post_meta($post, $class, $prefix=null, $separator='<BR/>')
		{
			if(qa_opt('network_site_enable')) {
				if(isset($post['who']['points']) && qa_opt('network_site_points')) {
					$points = intval(preg_replace('/[^\d.]/', '', $post['who']['points']['data']));
					$post['who']['points']['data']=$this->network_total_points($post['raw']['userid'],$points);
				}
				if (qa_opt('network_site_icons')) {
					$points = intval(preg_replace('/[^\d.]/', '', $post['who']['points']['data']));
					$post['who']['suffix'] = @$post['who']['suffix'].$this->network_user_sites($post['raw']['userid'],$points);
				}
			}
			qa_html_theme_base::post_meta($post, $class, $prefix, $separator);
			
		}		

	// worker
	
		var $network_points;

		function network_total_points($uid,$points) {
			if($this->network_points[$uid]) {
				foreach($this->network_points[$uid] as $point)
					$points+=$point;
			}
			else {
				$idx = 0;
				while(qa_opt('network_site_'.$idx.'_url')) {
					$point = (int)qa_db_read_one_value(
						qa_db_query_sub(
							'SELECT points FROM '.qa_db_escape_string(qa_opt('network_site_'.$idx.'_prefix')).'userpoints WHERE userid=#',
							$uid
						),
						true
					);
					$this->network_points[$uid][$idx] = $point;
					$points += $point;
					$idx++;
				}
			}
			return number_format($points);
		}
		
		function network_user_sites($uid,$this_points=null) {
			$idx = 0;
			$html = '';
			if(qa_opt('network_site_icon_this') && $this_points) {
				$html.= '<a class="qa-network-site-icon" href="'.qa_opt('site_url').'" title="'.qa_opt('site_title').': '.($this_points==1?qa_lang_html('main/1_point'):qa_lang_html_sub('main/x_points',number_format($this_points))).'"><img src="'.qa_opt('site_url').'favicon.ico"/></a>';
			}
			while(qa_opt('network_site_'.$idx.'_url')) {
				if($this->network_points[$uid]) {
						$points = $this->network_points[$uid][$idx];
				}
				else {
					$points = (int)qa_db_read_one_value(
						qa_db_query_sub(
							'SELECT points FROM '.qa_db_escape_string(qa_opt('network_site_'.$idx.'_prefix')).'userpoints WHERE userid=#',
							$uid
						),
						true
					);
					$this->network_points[$uid][$idx] = $points;
				}
				if($points < qa_opt('network_site_min_points')) {
					$idx++;
					continue;
				}
				
				$html.= '<a class="qa-network-site-icon" href="'.qa_opt('network_site_'.$idx.'_url').'" title="'.qa_opt('network_site_'.$idx.'_title').': '.($points==1?qa_lang_html('main/1_point'):qa_lang_html_sub('main/x_points',number_format($points))).'"><img src="'.qa_opt('network_site_'.$idx.'_url').qa_opt('network_site_'.$idx.'_icon').'"/></a>';
				$idx++;
			}
			return $html;					
		}


		function getuserfromhandle($handle) {
			require_once QA_INCLUDE_DIR.'qa-app-users.php';
			
			$publictouserid=qa_get_userids_from_public(array($handle));
			$userid=@$publictouserid[$handle];
				
			if (!isset($userid)) return;
			return $userid;
		}		
		// grab the handle from meta
		function who_to_handle($string)
		{
			preg_match( '#qa-user-link">([^<]*)<#', $string, $matches );
			return !empty($matches[1]) ? $matches[1] : null;
		}	
	}

