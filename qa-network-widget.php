<?php

	class qa_network_widget {

		function allow_template($template)
		{
			return true;
		}

		function allow_region($region)
		{
			return true;
		}

		function output_widget($region, $place, $themeobject, $template, $request, $qa_content)
		{
			
			$themeobject->output('<h2>'.qa_lang('network/widget_title').'</h2>');
			$idx = 0;
			if(qa_opt('network_site_widget_this')) {
				$html =  '<a class="qa-network-site-link" href="'.qa_opt('site_url').'" title="'.qa_opt('site_title').'"><img src="'.qa_opt('site_url').'favicon.ico"/>&nbsp;'.qa_opt('site_title').'</a>';
				$themeobject->output('<div class="network-site-widget-entry">',$html,'</div>');
			}
			while(qa_opt('network_site_'.$idx.'_url')) {
				
				$html = '<a class="qa-network-site-link" href="'.qa_opt('network_site_'.$idx.'_url').'" title="'.qa_opt('network_site_'.$idx.'_title').'"><img src="'.qa_opt('network_site_'.$idx.'_url').qa_opt('network_site_'.$idx.'_icon').'"/>&nbsp;'.qa_opt('network_site_'.$idx.'_title').'</a>';
				
				$themeobject->output('<div class="network-site-widget-entry">',$html,'</div>');
				
				$idx++;
			}
		}
	}


/*
	Omit PHP closing tag to help avoid accidental output
*/
