<?php
/*
	* Zoom
	* @Version 1.0.0
	* Developed by: Ami (亜美) Denault
*/

/*
	* Zoom
	* @Since 4.5.1
*/
declare(strict_types=1);

class Zoom
{

    private $errors, $baseUrl, $timeout;
    public $apiKey, $apiSecret, $zoomError;

    public function __construct($options = [])
    {

        $this->apiKey = Config::get('zoom/api_key');
        $this->apiSecret = Config::get('zoom/secret_key');
        $this->baseUrl = 'https://api.zoom.us/v2';
        $this->timeout = 30;

        foreach ($options as $key => $value) {
            if (property_exists($this, $key))
                $this->$key = $value;
        }
    }

/*
    * Create Headers
    * @ Since 4.5.1
    * @param ()
*/
    private function headers():array
    {
        return [
            'Authorization: Bearer ' .
                self::generateJWT($this->apiKey, $this->apiSecret),
            'Content-Type: application/json',
            'Accept: application/json',
        ];
    }

/*
    * Handle Error Request
    * @ Since 4.5.1
    * @param ()
*/
    function requestErrors():string
    {
        return $this->errors;
    }

/*
    * Handle Response Request
    * @ Since 4.5.1
    * @param ()
*/
    function responseCode():int
    {
        return $this->responseCode;
    }

/*
    * Create Valid Path
    * @ Since 4.5.1
    * @param (String path,Array Params)
*/
    private function pathReplace($path, $requestParams):string
    {
        $errors = [];
        $path = preg_replace_callback(
            '/\\{(.*?)\\}/',
            function ($matches) use ($requestParams, $errors) {
                if (!isset($requestParams[$matches[1]])) {
                    $this->errors[] =
                        'Required path parameter was not specified: ' .
                        $matches[1];
                    return '';
                }
                return rawurlencode($requestParams[$matches[1]]);
            },
            $path
        );

        if (count($errors))
            $this->errors = array_merge($this->errors, $errors);

        return $path;
    }

/*
    * Create Request
    * @ Since 4.5.1
    * @param (String method,String Path,Array QueryParams,Array PathParams,String body)
*/
    public function doRequest($method, $path, $queryParams = [], $pathParams = [], $body = ''):bool|object|array
    {
        if (is_array($body)) {
            if (!count($body)) {
                $body = '';
            } else {
                $body = json::encode($body);
            }
        }

        $this->errors = [];
        $this->responseCode = 0;

        $path = $this->pathReplace($path, $pathParams);

        if (count($this->errors))
            return false;

        $method = str::_strtoupper($method);
        $url = $this->baseUrl . $path;
        if (count($queryParams))
            $url .= '?' . http_build_query($queryParams);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers());
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (in_array($method, ['DELETE', 'PATCH', 'POST', 'PUT'])) {
            if ($method != 'DELETE' && strlen($body)) {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }

            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        $result = curl_exec($ch);

        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $this->responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return json::decode($result, true);
    }

/*
    * Generate JWT Token
    * @ Since 4.5.1
    * @param (String apykey,String apysecret)
*/
    public static function generateJWT($apiKey, $apiSecret):string
    {
        $token = ['iss' => $apiKey, 'exp' => time() + 60];
        $header = ['typ' => 'JWT', 'alg' => 'HS256'];

        $toSign = self::urlsafeB64Encode(json::encode($header)) .  '.' .  self::urlsafeB64Encode(json::encode($token));

        $signature = hash_hmac('SHA256', $toSign, $apiSecret, true);

        return $toSign . '.' . self::urlsafeB64Encode($signature);
    }

/*
    * Create URL Safe Base64 Encode
    * @ Since 4.5.1
    * @param (String string)
*/
    public static function urlsafeB64Encode($string):string
    {
        return str_replace('=', '', strtr(base64_encode($string), '+/', '-_'));
    }

/*
    * Generate Signature
    * @ Since 4.5.1
    * @param (String apykey,String api_secret,String meetingNumber, String Role)
*/
    public static function generateSignature($api_key, $api_secret, $meeting_number, $role):string
    {
        $time = time() * 1000 - 30000;

        $data = base64_encode($api_key . $meeting_number . $time . $role);

        $hash = hash_hmac('sha256', $data, $api_secret, true);

        $_sig =
            $api_key .
            '.' .
            $meeting_number .
            '.' .
            $time .
            '.' .
            $role .
            '.' .
            base64_encode($hash);

        return str::_rtrim(strtr(base64_encode($_sig), '+/', '-_'), '=');
    }

/*
    * Get User ID
    * @ Since 4.5.1
    * @param ()
*/
    public function getUserId():string|array
    {
        $response = $this->doRequest(
            'GET',
            '/users/{userId}',
            [],
            ['userId' => Config::get('zoom/email')]
        );

        if ($this->responseCode() == 200) {
            return $response['id'];
        } else {
            print_r($response);
            exit();
        }
    }

/*
    * Create Meeting
    * @ Since 4.5.1
    * @param (Array MeetingDetails)
*/
    public function create($meetingDetails):array|int
    {
        $response = $this->doRequest(
            'POST',
            '/users/{userId}/meetings',
            [],
            ['userId' => $this->getUserId()],
            json::encode($meetingDetails)
        );

        if ($this->responseCode() == 201) {
            return $response;
        } else {
            $this->zoomError = $response;
            return false;
        }
    }

/*
    * Get Templates
    * @ Since 4.5.1
    * @param ()
*/
    public function template():string|array
    {
        $response = $this->doRequest(
            'GET',
            '/users/{userId}/meeting_templates',
            [],
            ['userId' => Config::get('zoom/email')]
        );

        if ($this->responseCode() == 200) {
            return $response;;
        } else {
            print_r($response);
            exit();
        }
    }

/*
    * Update Meeting
    * @ Since 4.5.1
    * @param (Array MeetingDetails,String MeetingID)
*/
    public function update(array $meetingDetails,string $meetingId):array|int
    {
        $response = $this->doRequest(
            'PATCH',
            '/meetings/{meetingId}',
            [],
            ['meetingId' => $meetingId],
            json::encode($meetingDetails)
        );

        if ($this->responseCode() == 204) {
            return $response;
        } else {
            $this->zoomError = $response;

            return false;
        }
    }

/*
    * Delete Meeting
    * @ Since 4.5.1
    * @param (String MeetingID)
*/
    public function delete(string $meetingId):array|int
    {
        $response = $this->doRequest(
            'DELETE',
            '/meetings/{meetingId}',
            [],
            ['meetingId' => $meetingId],
            []
        );

        if ($this->responseCode() == 204) {
            return $response;
        } else {
            $this->zoomError = $response;

            return false;
        }
    }

/*
    * End Meeting
    * @ Since 4.5.1
    * @param (String MeetingID)
*/
    public function end(string $meetingId):array|int
    {
        $response = $this->doRequest(
            'PUT',
            '/meetings/{meetingId}/status',
            [],
            ['meetingId' => $meetingId],
            json::encode(['action' => 'end'])
        );

        if ($this->responseCode() == 204) {
            return $response;
        } else {
            $this->zoomError = $response;

            return false;
        }
    }

/*
    * List All Meeting
    * @ Since 4.5.1
    * @param ()
*/
    public function list():array|bool
    {
        $response = $this->doRequest(
            'GET',
            '/users/{userId}/meetings',
            [],
            ['userId' => $this->getUserId()],
            json::encode(['action' => 'end'])
        );

        if ($this->responseCode() == 204 || $this->responseCode() == 200) {
            return $response;
        } else {
            $this->zoomError = $response;

            return false;
        }
    }

/*
    * Meeting Information
    * @ Since 4.5.1
    * @param (String MeetingID)
*/
    public function meetingInfo(string $meetingId):array|bool
    {
        $response = $this->doRequest(
            'GET',
            '/meetings/{meetingId}',
            [],
            ['meetingId' => $meetingId],
            json::encode(['action' => 'end'])
        );


        if ($this->responseCode() == 204 || $this->responseCode() == 200) {
            return $response;
        } else {
            $this->zoomError = $response;

            return false;
        }
    }

 /*
    * List registrants
    * @ Since 4.5.1
    * @param (String MeetingID)
*/
    public function listRegistrants(string $meetingId):array|bool
    {
        $response = $this->doRequest(
            'GET',
            '/meetings/{meetingId}/registrants',
            [],
            ['meetingId' => $meetingId],
            json::encode(['action' => 'end'])
        );

        if ($this->responseCode() == 200) {
            return $response;
        } else {
            $this->zoomError = $response;

            return false;
        }
    }

/*
    * Add Registrant
    * @ Since 4.5.1
    * @param (String MeetingID,String Registrant)
*/
    public function addRegistrant(string $meetingId,string $registrant):array|bool
    {
        $response = $this->doRequest(
            'POST',
            '/meetings/{meetingId}/registrants',
            [],
            ['meetingId' => $meetingId],
            json::encode($registrant)
        );

        if ($this->responseCode() == 201) {
            return $response;
        } else {
            $this->zoomError = $response;
            return false;
        }
    }
}
