<?php

class IACAuth {

    const IACPath = 'api/v1/general/moodle_auth';
    private $client;
    private $fullUrl;
    private $delayTime;

    private $apiMis;
    private $apiPass;

    public function __construct($url, $delayTime, $apiMis, $apiPass ) {

        $this->delayTime = $delayTime;
        $this->fullUrl = $url.self::IACPath;
        $this->apiMis = $apiMis;
        $this->apiPass = $apiPass;

    }

    public function user_login($username, $password) {

        $IACAuth = $this->verifyIAC($username,$password);
        if ($IACAuth)
            {
                return true;
            }
        return false;

    }

    private function verifyIAC($username,$password)
    {
	$returnVal = false;
        try
        {
            // Endpoint URL
            $url = $this->fullUrl;

            // Initialize cURL session
            $ch = curl_init($url);

            // Prepare POST fields
            $postFields = array(
                'username' => $username,
                'password' => $password,
                'host_mis' => $this->apiMis,
                'host_pass' => $this->apiPass
            );

            $postJson = json_encode($postFields);
            try
            {
                // Get delay time in base 10
                $delayTime = intval($this->delayTime,10);
            }
            catch (Exception $ex)
            {
                // Default to 10 seconds if original input
                // is not a number
                $delayTime = 10; 
            }

            // Set cURL options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postJson);
            curl_setopt($ch, CURLOPT_TIMEOUT, $delayTime); // Set timeout (seconds)
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($postJson)
            ));

            // Execute cURL request
            $response = curl_exec($ch);

            // Error handling
            if (strlen($response) === 0) {
                $return_val = False;
            } else {
                $return_val = json_decode($response) -> status;
            }

            // Close cURL session
            curl_close($ch);

            // Output the result
            return $return_val;
        }
        catch (Exception $ex)
        {
            return FALSE;
        }
    
    }


}

?>