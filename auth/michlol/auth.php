<?php

/**
 * Authentication Plugin: Michlol Authentication
 * Authenticates user with Michlol's WsM3API
 *
 * @package    auth
 * @subpackage michlol
 * @copyright  2024 onwards IAC (https://iac.ac.il)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/authlib.php');
require_once(dirname(__FILE__).'/classes/michlolauth.php');
require_once(dirname(__FILE__).'/classes/iacauth.php');

/**
 * Michlol authentication plugin.
 *
 * @package    auth
 * @subpackage michlol
 * @copyright  2024 onwards IAC (https://iac.ac.il)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_plugin_michlol extends auth_plugin_base {



    /**
     * The name of the component, 
     * used to request admin settings.
     */
    const COMPONENT_NAME = 'auth_michlol';
    /**
     * 
     * Constructor.
     */
      function __construct(){
        $this->authtype = 'michlol';
        $this->config =  get_config(self::COMPONENT_NAME);
    }



    public function auth_plugin_michlol() {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    }

    /**
     * Returns true if the username and password work and false if they are
     * wrong or don't exist. (Non-mnet accounts only!)
     *
     * @param string $username The username
     * @param string $password The password
     * @return bool Authentication success or failure.
     */
    function user_login($username, $password) 
    {
        global $CFG, $DB, $USER;

            //If the user comes from michlol use manual auth
            if ($_SERVER['REQUEST_URI'] == '/local/ws_rashim/login.php') {
                if ($this->rashim_sso($username, $password)) 
                {
                    return TRUE;
                }  
                else 
                {
                    return false;
                }
        }

        if (!$user = $DB->get_record('user', array('username'=>$username, 'mnethostid'=>$CFG->mnet_localhost_id)))
        {
            return false;
        }

        $useMichAPI = $this->config->use_michlol;
        # Michlol Api user (MICHAPI)
        $apiMis = $this->config->auth_michlol_mis;
        $apiLogin = $this->config->auth_michlol_user;
        $apiPass = $this->config->auth_michlol_password;

        // Try authenticating with IAC
        try
        {
            $iacUrl = $this->config->auth_iac_address;
            $delayTime = $this->config->auth_iac_delay;
            $IAC = new IACAuth($iacUrl,$delayTime,$apiMis,$apiPass );
            if ($IAC->user_login(stripslashes($username), stripslashes($password))) 
            {
                return TRUE;
            } 
        }
        catch (Exception $ex)
        {
            // TODO: write exception to log
        }

        // Try authenticating with WsM3API
        if ($useMichAPI === "1")
        {
            $michlol = new MichlolAuth($this->config->auth_michlol_addres_key, $apiLogin, $apiPass);

            if ($michlol->user_login(stripslashes($username), stripslashes($password))) 
            {
                return TRUE;
            } 
            else 
            {
                return FALSE;
            }
        }

        return FALSE;
    }

    public function rashim_sso($username, $password) {
        global $DB;
    //    if (substr($_SERVER['HTTP_REFERER'],-19) == "ws_rashim/login.php") {
	if($_SERVER['REQUEST_URI'] == '/local/ws_rashim/login.php') {
            //$this->debug("\n\nReferer:".substr($_SERVER['HTTP_REFERER'],-19)."\n:".$username.":".$password.":\n");
	    if ($user = $DB->get_record('user', array('username' => $username))) {
                if($rashim_pass = $DB->get_record('rashim_passwords', ['username' => $user->username])) {
                        if($rashim_pass->password == $password) {
                                return true;
                        }
                }

      /*      if ($user = $DB->get_record('user', array('username' => $username))) {
                $validate = validate_internal_user_password($user, $password);
		//print_r($user);
		//echo md5($password);	
		//die();
                //$this->debug("md5 of inserted password:".md5($password)."\nstored password:".$user->password."\nvalidate:".intval($validate)."\n");
                return $validate;
            } */
            }
        }
	return false;
    }


    /**
     * Updates the user's password.
     *
     * Called when the user password is updated.
     *
     * @param  object  $user        User table object
     * @param  string  $newpassword Plaintext password
     * @return boolean result
     */
    function user_update_password($user, $newpassword) {
	global $DB;
        $user = get_complete_user_data('id', $user->id);
	if($rashim_pass = $DB->get_record('rashim_passwords', ['username' => $user->username])) {
		$rashim_pass->password = $newpassword;
		$update = $DB->update_record('rashim_passwords', $rashim_pass);
	} else {
		$rashim_pass = new stdclass();
		$rashim_pass->username = $user->username;
		$rashim_pass->password = $newpassword;
		$update = $DB->insert_record('rashim_passwords', $rashim_pass);
	}
	print_r($update);
	return $update ? true: false;
//        return update_internal_user_password($user, $newpassword);
    }

    function prevent_local_passwords() {
        return true;
    }

    /**
     * Returns true if this authentication plugin is 'internal'.
     *
     * @return bool
     */
    function is_internal() {
        return false;
    }

    /**
     * Returns true if this authentication plugin can change the user's
     * password.
     *
     * @return bool
     */
    function can_change_password() {
        return true;
    }

    /**
     * Returns the URL for changing the user's pw, or empty if the default can
     * be used.
     *
     * @return moodle_url
     */
    function change_password_url() {
        return null;
    }

    /**
     * Returns true if plugin allows resetting of internal password.
     *
     * @return bool
     */
    function can_reset_password() {
        return true;
    }

    /**
     * Processes and stores configuration data for this authentication plugin.
     *
     * @param array $config
     * @return void
     */
    function process_config($config) {

        if(!isset($config->auth_rashim_addres))   {
                $config->auth_rashim_addres = '';
        }
        if(!isset($config->auth_rashim_debug))   {
                    $config->auth_rashim_debug = 1;
        }
        if(!isset($config->auth_rashim_debug_to_log))   {
                    $config->auth_rashim_debug_to_log = 1;
        }
        if(!isset($config->auth_rashim_debug_log_file))   {
                    $config->auth_rashim_debug_log_file = '';
        }

        set_config('auth_rashim_addres', $config->auth_rashim_addres, 'auth/rashim');
        set_config('auth_rashim_debug', $config->auth_rashim_debug, 'auth/rashim');
        set_config('auth_rashim_debug_to_log', $config->auth_rashim_debug_to_log, 'auth/rashim');
        set_config('auth_rashim_debug_log_file', $config->auth_rashim_debug_log_file, 'auth/rashim');

        return true;
    }

   /**
    * Confirm the new user as registered. This should normally not be used,
    * but it may be necessary if the user auth_method is changed to manual
    * before the user is confirmed.
    *
    * @param string $username
    * @param string $confirmsecret
    */
    function user_confirm($username, $confirmsecret = null) {
        global $DB;

        $user = get_complete_user_data('username', $username);

        if (!empty($user)) {
            if ($user->confirmed) {
                return AUTH_CONFIRM_ALREADY;
            } else {
                $DB->set_field("user", "confirmed", 1, array("id"=>$user->id));
                if ($user->firstaccess == 0) {
                    $DB->set_field("user", "firstaccess", time(), array("id"=>$user->id));
                }
                return AUTH_CONFIRM_OK;
            }
        } else  {
            return AUTH_CONFIRM_ERROR;
        }
    }

}


