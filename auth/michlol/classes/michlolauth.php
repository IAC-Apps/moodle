<?php


/**
 * Authenticate with Michlol WsM3API endpoint.
 * This authentication method uses SOAP1.2 
 * to handle the request to the server
 */
class MichlolAuth {

    const michlolPath = 'WsM3Api/MichlolApi.asmx?WSDL';
    private $client;
    private $api_login;
    private $api_password;
    
    /**
     * 
     * Constructor.
     * @param string $url = Server url (e.g. https://my.iac.ac.il/).
     * @param string $api_login = Michlol first name of API user (e.g. MICHAPI).
     * @param string $api_password = Michlol password of API user .
     */
    public function __construct($url, $api_login, $api_password,$use_https) {

        $this->api_login = $api_login;
        $this->api_password = $api_password;
        //error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);

        if ($use_https === "1")
        {
            $full_url = "https://".$url.'/'.self::michlolPath;
        }
        else
        {
            $full_url = "http://".$url.'/'.self::michlolPath;
        }

        // Prepare post request

        $this->client = new SoapClient($full_url,
            array(
                'exceptions' => true,
                'trace' => true,
                'soap_version' => SOAP_1_2,
                'encoding' => 'UTF-8',
                'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
                'cache_wsdl' => WSDL_CACHE_NONE
            ));
    }

    /**
     * 
     * function to authenticate inputted credentials.
     * 
     * @param string $username = Username requesting to login
     * @param int $password = Password of user requesting to login.
     * @return bool Authentication success or failure.
     */
    public function user_login($username, $password) {
        global $CFG;

        // Try to login as a student
        if ($this->auth($this->studentXML($username, $password),$username)) {
            return true;
        }
        // Try to login as a lecturer 
        else if ($this->auth($this->teacherXML($username, $password),$username)) {
            return true;
        }
        return false;
    }

    public function auth($xml,$username) {

        // Create Request body.
        $param = array(
            'P_RequestParams' => array(
                'RequestID' => 111,
                'InputData' => $xml
            ),
            'Authenticator' => array(
                'UserName' => $this->api_login, // Michlol USR username taken from settings
                'Password' => $this->api_password, // Michlol USR password taken from settings
            ));
        
        // send SOAP request.
        $result = $this->client->ProcessRequest($param);

        // Check if input isn't empty.
        if (empty($result->ProcessRequestResult->OutputData)) {
            return false;
        }

        // Convert XML string to a SimpleXMLElement object.
        $xml_result = new SimpleXMLElement($result->ProcessRequestResult->OutputData);
        
        // Check if authentication was successfull.
        if (($xml_result->RECORD->LOGIN_RESULT == 10) && (!strcmp($xml_result->RECORD->LOGIN_USERNAMENAME,$username))){
            return true;
        } else {
            return false;
        }
    }

    /**
     * 
     * Create XML request for student.
     * 
     * @param string $username = Username requesting to login
     * @param int $password = Password of user requesting to login.
     * @return string XML request body to send out.
     */
    public function studentXML($username, $password) {
        return <<<EOXML
<?xml version="1.0" encoding="utf-8" ?>
<PARAMS>
	<PM_ZHT></PM_ZHT>
	<PM_USERNAME></PM_USERNAME>
	<PM_PASSWORD></PM_PASSWORD>
	<PT_ZHT>$username</PT_ZHT>
	<PT_SECRETCODE></PT_SECRETCODE>
	<PT_USERNAME></PT_USERNAME>
	<PT_INTERNETPASSWORD></PT_INTERNETPASSWORD>
	<PT_PASSWORD>$password</PT_PASSWORD>
</PARAMS>
EOXML;
    }

    /**
     * 
     * Create XML request for teacher/lecturer.
     * 
     * @param string $username = Username requesting to login
     * @param int $password = Password of user requesting to login.
     * @return string XML request body to send out.
     */
    public function teacherXML($username, $password) {
        return <<<EOXML
<?xml version="1.0" encoding="utf-8" ?>
<PARAMS>
        <PM_ZHT>$username</PM_ZHT>
        <PM_USERNAME></PM_USERNAME>
        <PM_PASSWORD>$password</PM_PASSWORD>
        <PT_ZHT></PT_ZHT>
        <PT_SECRETCODE></PT_SECRETCODE>
        <PT_USERNAME></PT_USERNAME>
        <PT_INTERNETPASSWORD></PT_INTERNETPASSWORD>
        <PT_PASSWORD></PT_PASSWORD>
</PARAMS>
EOXML;
    }

}

?>