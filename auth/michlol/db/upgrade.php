<?php

function xmldb_auth_rashim_upgrade($oldversion = 0) {
        global $DB;
        $dbman = $DB->get_manager();
        if ($oldversion <  2017103000) {
                $table = new xmldb_table('rashim_passwords');
                $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
                $table->add_field('username', XMLDB_TYPE_CHAR, '100', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0);
                $table->add_field('password', XMLDB_TYPE_CHAR, '255', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0);
                $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
                if(!$dbman->table_exists($table)) {
                        $dbman->create_table($table);
                }
                upgrade_plugin_savepoint(true, 2017103000, 'auth', 'rashim');
        }
/*	if ($oldversion <  2017103000) {
		$table = new xmldb_table('rashim_passwords');
		$table->add_index('username', XMLDB_INDEX_UNIQUE, ['username']);
*/		
	return true;
}
