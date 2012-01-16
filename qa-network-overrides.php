<?php
		
	function qa_db_add_table_prefix($rawname) {
		global $migrate_change_db;
		if($migrate_change_db) {
			return $migrate_change_db.$rawname;
		}
		else return qa_db_add_table_prefix_base($rawname);
	}
						
/*							  
		Omit PHP closing tag to help avoid accidental output
*/							  
						  

