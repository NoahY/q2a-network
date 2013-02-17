<?php

/*
	Plugin Name: Network Sites
	Plugin URI: https://github.com/NoahY/q2a-network
	Plugin Description: Allows networking of sites
	Plugin Version: 0.5
	Plugin Date: 2012-01-13
	Plugin Author: NoahY
	Plugin Author URI: http://www.question2answer.org/
	Plugin License: GPLv2
	Plugin Minimum Question2Answer Version: 1.5
	Plugin Update Check URI: https://raw.github.com/NoahY/q2a-network/master/qa-plugin.php
*/


	if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../../');
		exit;
	}


	#qa_register_plugin_module('page', 'qa-faq-page.php', 'qa_faq_page', 'FAQ Page');
	qa_register_plugin_layer('qa-network-layer.php', 'Network Layer');	
	qa_register_plugin_module('module', 'qa-network-admin.php', 'qa_network_admin', 'Network Site Admin');
	qa_register_plugin_module('module', 'qa-network-migrate-admin.php', 'qa_network_migrate_admin', 'Network Site Post Migration');
	qa_register_plugin_phrases('qa-network-lang-*.php', 'network');
	qa_register_plugin_module('widget', 'qa-network-widget.php', 'qa_network_widget', 'Network Sites Widget');
	qa_register_plugin_overrides('qa-network-overrides.php');


/*
	Omit PHP closing tag to help avoid accidental output
*/