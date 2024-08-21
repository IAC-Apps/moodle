<?php

class MichlolAuth {

    const michlolPath = 'WsM3Api/MichlolApi.asmx?WSDL';
    private $client;
    private $api_login;
    private $api_password;
    
    // Constructor function for MichlolAuth
    public function __construct($url, $api_login, $api_password) {

        $this->api_login = $api_login;
        $this->api_password = $api_password;
        //error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
        $this->client = new SoapClient($url.self::michlolPath,
            array(
                'exceptions' => true,
                'trace' => true,
                'soap_version' => SOAP_1_2,
                'encoding' => 'UTF-8',
                'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
                'cache_wsdl' => WSDL_CACHE_NONE
            ));
    }

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

        $param = array(
            'P_RequestParams' => array(
                'RequestID' => 111,
                'InputData' => $xml
            ),
            'Authenticator' => array(
                'UserName' => $this->api_login, // Michlol USR username taken from settings
                'Password' => $this->api_password, // Michlol USR password taken from settings
            ));
        
        // send SOAP request
        $result = $this->client->ProcessRequest($param);

        if (empty($result->ProcessRequestResult->OutputData)) {
            return false;
        }

        $xml_result = new SimpleXMLElement($result->ProcessRequestResult->OutputData);

        if (($xml_result->RECORD->LOGIN_RESULT == 10) && (!strcmp($xml_result->RECORD->LOGIN_USERNAMENAME,$username))){
	  //        if ($xml_result->RECORD->LOGIN_RESULT == 10) {
            return true;
        } else {
            return false;
        }
    }

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