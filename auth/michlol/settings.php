<?php
// This file is part of michlol auth plugin
//
// 
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Admin settings and defaults.
 *
 * @package    auth_michlol
 * @copyright  2024 IAC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    /********** Legacy michlol Authentication Settings **********/
    $settings->add(new admin_setting_heading('auth_michlol/pluginname', 
        new lang_string('legacy_michlol','auth_michlol'),
        new lang_string('legacy_michlol_desc', 'auth_michlol')));

    // Should we use Michlol Soap authentication?
    $usemichlolAuth = array(
        FALSE => get_string('no','auth_michlol'),
        TRUE => get_string('yes','auth_michlol'),
    );
    // Show $useMichlolAuth as a setting to the user
    $settings->add(new admin_setting_configselect('auth_michlol/use_michlol',
        new lang_string('use_michlol', 'auth_michlol'),
        new lang_string('use_michlol_desc', 'auth_michlol'), 1, $usemichlolAuth));

    // Michlol USR MIS
    $settings->add(new admin_setting_configtext('auth_michlol/auth_michlol_mis', 
    get_string('auth_michlol_mis', 'auth_michlol'),
        get_string('auth_michlol_mis_desc', 'auth_michlol'), 90, PARAM_INT ));

    // Michlol USR username
    $settings->add(new admin_setting_configtext('auth_michlol/auth_michlol_user', 
    get_string('auth_michlol_username', 'auth_michlol'),
        get_string('auth_michlol_username_desc', 'auth_michlol'), "MICHAPI", PARAM_RAW_TRIMMED));
    
    // Michlol USR password
    $settings->add(new admin_setting_configpasswordunmask('auth_michlol/auth_michlol_password',
            get_string('auth_michlol_password', 'auth_michlol'),
            get_string('auth_michlol_password_desc', 'auth_michlol'), ''));
    
    // Michlol server address
    $settings->add(new admin_setting_configtext('auth_michlol/auth_michlol_addres_key', get_string('auth_michlol_address_key', 'auth_michlol'),
                    get_string('auth_michlol_address', 'auth_michlol'), '', PARAM_RAW_TRIMMED));

    /********** Ramat Gan Academic College(IAC) Authentication **********/ 
    $settings->add(new admin_setting_heading('auth_michlol/iac_auth', 
    new lang_string('iac_michlol','auth_michlol'),
    new lang_string('iac_michlol_desc', 'auth_michlol')));

    // Delay time
    $settings->add(new admin_setting_configtext('auth_michlol/auth_iac_delay', 
    get_string('auth_iac_delay_str', 'auth_michlol'),
        get_string('auth_iac_delay_desc', 'auth_michlol'), 10, PARAM_INT ));

    // IAC server address
    $settings->add(new admin_setting_configtext('auth_michlol/auth_iac_address',
     get_string('auth_iac_address_str', 'auth_michlol'),
        get_string('auth_iac_address_desc', 'auth_michlol'), 'https://server.iac.ac.il/', PARAM_RAW_TRIMMED));
    
    // LOG settings ( don't do anything yet? )
    // $options = array(0 => get_string('no'), 1 => get_string('yes'));

    // $settings->add(new admin_setting_configselect('auth_michlol/auth_michlol_debug_key', get_string('auth_michlol_debug_key', 'auth_michlol'),
    //                     get_string('auth_michlol_debug', 'auth_michlol'), 0, $options));
    // $settings->add(new admin_setting_configselect('auth_michlol/auth_michlol_debug_to_log_key', get_string('auth_michlol_debug_to_log_key', 'auth_michlol'),
    //                     get_string('auth_michlol_debug_to_log', 'auth_michlol'), 0, $options));

    // $settings->add(new admin_setting_configtext('auth_michlol/auth_michlol_debug_log_file_key', get_string('auth_michlol_debug_log_file_key', 'auth_michlol'),
    //                 get_string('auth_michlol_debug_log_file', 'auth_michlol'), '', PARAM_TEXT));


    // Display locking / mapping of profile fields.
    $authplugin = get_auth_plugin('michlol');
    display_auth_lock_options($settings, $authplugin->authtype, $authplugin->userfields,
    get_string('auth_fieldlocks_help', 'auth'), false, false);



}
