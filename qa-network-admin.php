<?php
    class qa_network_admin {

		function allow_template($template)
		{
			return ($template!='admin');
		}

		function option_default($option) {
			
			$idx = 0;
			
			switch($option) {
			case 'network_site_css':
				return '.qa-network-site-link {
	font-size:110%;
	padding-bottom:8px;
	font-weight:bold;
	color:blue;
}
.qa-network-site-icon {
	vertical-align:sub;
}';
			case 'network_site_min_points':
				return 100;
			default:
				return null;
			}

		}

		function admin_form(&$qa_content)
		{

		//	Process form input
			$ok = null;

		
			if (qa_clicked('network_site_save')) {

				qa_opt('network_site_enable',(bool)qa_post_text('network_site_enable'));
				qa_opt('network_site_points',(bool)qa_post_text('network_site_points'));
				qa_opt('network_site_icons',(bool)qa_post_text('network_site_icons'));
				qa_opt('network_site_icon_this',(bool)qa_post_text('network_site_icon_this'));
				qa_opt('network_site_min_points',(int)qa_post_text('network_site_min_points'));
				qa_opt('network_site_widget_this',(bool)qa_post_text('network_site_widget_this'));
				qa_opt('network_site_css',qa_post_text('network_site_css'));

				$idx = 0;
				while($idx <= (int)qa_post_text('network_site_number')) {
					qa_opt('network_site_'.$idx,qa_post_text('network_site_'.$idx));
					qa_opt('network_site_'.$idx.'_title',qa_post_text('network_site_'.$idx.'_title'));
					qa_opt('network_site_'.$idx.'_prefix',qa_post_text('network_site_'.$idx.'_prefix'));
					qa_opt('network_site_'.$idx.'_url',qa_post_text('network_site_'.$idx.'_url'));
					qa_opt('network_site_'.$idx.'_icon',qa_post_text('network_site_'.$idx.'_icon'));
					$idx++;
				}
				
				$ok = qa_lang('admin/options_saved');
			}
			else if (qa_clicked('network_site_reset')) {
				foreach($_POST as $i => $v) {
					$def = $this->option_default($i);
					if($def !== null) qa_opt($i,$def);
				}
					
				$idx = 0;
				while($idx <= (int)qa_post_text('network_site_number')) {
					qa_opt('network_site_'.$idx,$this->option_default('network_site_'.$idx)?$this->option_default('network_site_'.$idx):'');
					qa_opt('network_site_'.$idx.'_title',$this->option_default('network_site_'.$idx.'_title')?$this->option_default('network_site_'.$idx.'_title'):'');
					qa_opt('network_site_'.$idx.'_prefix',$this->option_default('network_site_'.$idx.'_prefix')?$this->option_default('network_site_'.$idx.'_prefix'):'');
					qa_opt('network_site_'.$idx.'_url',$this->option_default('network_site_'.$idx.'_url')?$this->option_default('network_site_'.$idx.'_url'):'');
					qa_opt('network_site_'.$idx.'_icon',$this->option_default('network_site_'.$idx.'_icon')?$this->option_default('network_site_'.$idx.'_icon'):'');
					$idx++;
				}

				$ok = qa_lang('admin/options_reset');
			}

		// Create the form for display

			$fields = array();


			$fields[] = array(
				'label' => 'Enable network sites',
				'tags' => 'NAME="network_site_enable"',
				'value' => qa_opt('network_site_enable'),
				'type' => 'checkbox',
			);
				
			
			$fields[] = array(
				'label' => 'Network custom css',
				'tags' => 'NAME="network_site_css"',
				'value' => qa_opt('network_site_css'),
				'type' => 'textarea',
				'rows' => 20
			);
			$fields[] = array(
				'type' => 'blank',
			);

			$fields[] = array(
				'label' => 'Replace points with network points',
				'tags' => 'NAME="network_site_points"',
				'value' => qa_opt('network_site_points'),
				'type' => 'checkbox',
			);
				
			$fields[] = array(
				'label' => 'Add network site icons to user meta',
				'tags' => 'NAME="network_site_icons"',
				'value' => qa_opt('network_site_icons'),
				'type' => 'checkbox',
			);
			$fields[] = array(
				'label' => 'Include this site icon',
				'tags' => 'NAME="network_site_icon_this"',
				'value' => qa_opt('network_site_icon_this'),
				'type' => 'checkbox',
			);
			$fields[] = array(
				'label' => 'Include this site in widget',
				'tags' => 'NAME="network_site_widget_this"',
				'value' => qa_opt('network_site_widget_this'),
				'type' => 'checkbox',
			);				
			$fields[] = array(
				'label' => 'Min site points to add site icon',
				'tags' => 'NAME="network_site_min_points"',
				'value' => qa_opt('network_site_min_points'),
				'type' => 'number',
			);

				
				

			$fields[] = array(
				'type' => 'blank',
			);

			$sections = '<div id="qa-network-site-sections">';

			$idx = 0;
			while(qa_opt('network_site_'.$idx.'_url')) {
				$sections .='
<table id="qa-network-site-section-table-'.$idx.'" width="100%">
	<tr>
		<td>
			<b>Site #'.($idx+1).' title:</b><br/>
			<input class="qa-form-tall-text" type="text" value="'.qa_opt('network_site_'.$idx.'_title').'" id="network_site_'.$idx.'_title" name="network_site_'.$idx.'_title"><br/><br/>
			<b>Site #'.($idx+1).' table prefix:</b><br/>
			<input class="qa-form-tall-text" type="text" value="'.qa_opt('network_site_'.$idx.'_prefix').'" id="network_site_'.$idx.'_prefix" name="network_site_'.$idx.'_prefix"><br/><br/>
			<b>Site #'.($idx+1).' url:</b><br/>
			<input class="qa-form-tall-text" type="text" value="'.qa_opt('network_site_'.$idx.'_url').'" id="network_site_'.$idx.'_url" name="network_site_'.$idx.'_url"><br/><br/>
			<b>Site #'.($idx+1).' icon (relative to url):</b><br/>
			<input class="qa-form-tall-text" type="text" value="'.qa_opt('network_site_'.$idx.'_icon').'" id="network_site_'.$idx.'_icon" name="network_site_'.$idx.'_icon"><br/><br/>
		</td>
	</tr>
</table>
<hr/>';

				$idx++;
			}
			$sections .= '</div>';

			$fields[] = array(
				'type' => 'static',
				'value' => $sections
			);


			$fields[] = array(
				'type' => 'static',
				'value' =>'
<script>
	var next_network_site = '.$idx.'; 
	function addNetworkSite(){
		jQuery("#qa-network-site-sections").append(\'<table id="qa-network-site-section-table-\'+next_network_site+\'" width="100%"><tr><td><b>Site #\'+(next_network_site+1)+\' title:</b><br/><input class="qa-form-tall-text" type="text" value="" id="network_site_\'+next_network_site+\'_title" name="network_site_\'+next_network_site+\'_title"><br/><br/><b>Site #\'+(next_network_site+1)+\' table prefix:</b><br/><input class="qa-form-tall-text" type="text" value="" id="network_site_\'+next_network_site+\'_prefix" name="network_site_\'+next_network_site+\'_prefix"><br/><br/><b>Site #\'+(next_network_site+1)+\' url:</b><br/><input class="qa-form-tall-text" type="text" value="" id="network_site_\'+next_network_site+\'_url" name="network_site_\'+next_network_site+\'_url"><br/><br/><b>Site #\'+(next_network_site+1)+\' icon (relative to url):</b><br/><input class="qa-form-tall-text" type="text" value="favicon.ico" id="network_site_\'+next_network_site+\'_icon" name="network_site_\'+next_network_site+\'_icon"><br/><br/></td></tr></table><hr/>\');
		
		next_network_site++;
		jQuery("input[name=network_site_number]").val(next_network_site);
	}
</script>
<input type="button" value="add site" onclick="addNetworkSite()">'
			);

			return array(           
				'ok' => ($ok && !isset($error)) ? $ok : null,
					
				'fields' => $fields,
				
				'hidden' => array(
					'network_site_number' => $idx
				),
				 
				'buttons' => array(
					array(
					'label' => qa_lang_html('main/save_button'),
					'tags' => 'NAME="network_site_save"',
					),
					array(
					'label' => qa_lang_html('admin/reset_options_button'),
					'tags' => 'NAME="network_sitereset"',
					),
				),
			);
		}
    }