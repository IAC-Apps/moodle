<?php


/**
 * Authenticate with IAC servers
 * This authentication method uses HTTP POST
 * to handle the request to the server
 */
class IACAuth {

    const IACPath = 'api/v1/general/moodle_auth';
    private $client;
    private $full_url;
    private $delayTime;

    private $apiMis;
    private $apiPass;

    /**
     * 
     * Constructor.
     * @param string $url = Server url (e.g. https://server.iac.ac.il/).
     * @param int $delayTime = time to wait for server response.
     * @param string $apiMis = Michlol mis of API user (e.g. user MICHAPI has mis of 90 ).
     * @param string $apiPass = Michlol password of API user .
     */
    public function __construct($url, $delayTime, $apiMis, $apiPass, $use_https ) {

        $this->delayTime = $delayTime;
        $this->apiMis = $apiMis;
        $this->apiPass = $apiPass;

        if ($use_https === "1")
        {
            $this->full_url = "https://".$url.'/'.self::IACPath;
        }
        else
        {
            $this->full_url = "http://".$url.'/'.self::IACPath;
        }




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

        // Call authentication server
        $IACAuth = $this->verifyIAC($username,$password);
        if ($IACAuth)
            {
                return true;
            }
        return false;

    }


    /**
     * 
     * Call authentication server using POST request.
     * 
     * @param string $username = Username requesting to login
     * @param int $password = Password of user requesting to login.
     * @return bool Authentication success or failure.
     */
    private function verifyIAC($username,$password)
    {
	$returnVal = false;
        try
        {
            
            // Endpoint URL
            $url = $this->full_url;

            // Initialize cURL session
            $ch = curl_init($url);

            // Prepare POST body
            $postFields = array(
                'username' => $username,
                'password' => $password,
                'host_mis' => $this->apiMis,
                'host_pass' => $this->apiPass
            );

            // Encode $postFields into a JSON string
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