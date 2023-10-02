<?php

namespace Payment\RozetkaPay;

use Payment\RozetkaPay\Model\PaymentCreateRequest;
use Payment\RozetkaPay\Model\PaymentRequest;
use Payment\RozetkaPay\Model\TransactionDetails;

use Payment\RozetkaPay\Model\PaymentsResponsesSuccess;
use Payment\RozetkaPay\Model\PaymentsResponsesError;


class RozetkaPay {
    
    const version = 'v1';

    const urlBase = 'https://api.rozetkapay.com/api/';
    
    const testLogin = 'a6a29002-dc68-4918-bc5d-51a6094b14a8';
    const testPassword = 'XChz3J8qrr';
    
    private $token = '';
    private $headers = array();
    private $callback_url = '';
    private $result_url = '';
    private $currency = 'UAH';

    public function __construct() {
        $this->headers[] = 'Content-Type: application/json';
    }

    public function getCallbackURL() {
        return $this->callback_url;
    }

    public function getResultURL() {
        return $this->result_url;
    }

    public function setCallbackURL($callback_url) {
        $this->callback_url = str_replace("&amp;", "&", $callback_url);
    }

    public function setResultURL($result_url) {
        $this->result_url = str_replace("&amp;", "&", $result_url);
    }

    public function setBasicAuth($login, $password) {

        $this->token = base64_encode($login . ":" . $password);
        $this->headers[] = 'Authorization: Basic ' . $this->token;
    }
    
    public function setBasicAuthTest($login = '', $password = '') {
        
        $this->setBasicAuth(
                empty($login)?self::testLogin:$login, 
                empty($password)?self::testPassword:$password
        );
        
    }

    public function valideCurrency($data) {

        if (!isset($data['currency']) || empty($data['currency'])) {
            $data['currency'] = $this->currency;
        }

        return $data;
    }

    public function paymentCreate(PaymentCreateRequest $data) {

        if (empty($data->callback_url)) {
            $data->callback_url = $this->getCallbackURL();
        }
        
        if (empty($data->result_url)) {
            $data->result_url = $this->getResultURL();
        }

        if ($data->amount <= 0) {
            throw new \Exception('Fatal error: amount!');
        }

        $data = (array) ($data);
        
        $data['external_id'] = (string)$data['external_id'];

        foreach ($data as $key => $value) {
            if (is_null($value) || empty($value)) {
                unset($data[$key]);
            }
        }

        list($jsonResponse, $headerCode) = $this->sendRequest("payments/".self::version."/new", "POST", $data);
        
        if($headerCode == 200){            
            $result = new PaymentsResponsesSuccess($jsonResponse); 
            return [$result, false];
        }else{ 
            $result = new PaymentsResponsesError($jsonResponse);     
            return [false, $result];
        }
    }
    
    public function paymentConfirm($data) {
        
        if (empty($data->callback_url)) {
            $data->callback_url = $this->getCallbackURL();
        }

        if ($data->amount <= 0) {
            throw new \Exception('Fatal error: amount!');
        }
        
        list($jsonResponse, $headerCode) = $this->sendRequest("payments/".self::version."/confirm", "POST", $data);
        
        if($headerCode == 200){            
            $result = new PaymentsResponsesSuccess($jsonResponse);
            return [$result, false];
        }else{            
            $result = new PaymentsResponsesError($jsonResponse);  
            return [false, $result->code];
        }
        
    }
    
    public function paymentCancel($data) {
        
        $result = $this->sendRequest("payments/".self::version."/cancel", "POST", $data);
        
        if($headerCode == 200){            
            $result = new PaymentsResponsesSuccess($jsonResponse);            
        }else{            
            $result = new PaymentsResponsesError($jsonResponse);            
        }
        
    }

    public function paymentRefund(PaymentRequest $data) {
        
        if (empty($data->callback_url)) {
            $data->callback_url = $this->getCallbackURL();
        }

        if (empty($data->result_url)) {
            $data->result_url = $this->getResultURL();
        }
        
        $data = (array) ($data);
        
        $data['external_id'] = (string)$data['external_id'];
        
        foreach ($data as $key => $value) {
            if (is_null($value) || empty($value)) {
                unset($data[$key]);
            }
        }
        
        list($jsonResponse, $headerCode) = $this->sendRequest("payments/".self::version."/refund", "POST", $data);
        
        if($headerCode == 200){
            
            $result = new PaymentsResponsesSuccess($jsonResponse);
            
        }else{
            
            $result = new PaymentsResponsesError($jsonResponse);
            
        }
        
        if(isset($result->is_success)){           
            return [$result->details->status, false];
        }else{
            return [false, $result];
        }
    }
    
    public function paymentInfo($external_id) {
        
        list($jsonResponse, $headerCode) = $this->sendRequest("payments/".self::version."/info?external_id=" . $external_id);
        
        if($headerCode == 200){
            
            $result = [];
            $result['purchase_details'] = [];
            $result['confirmation_details'] = [];
            $result['cancellation_details'] = [];
            $result['refund_details'] = [];
            
            if(isset($jsonResponse['purchase_details'])){                
                foreach ($jsonResponse['purchase_details'] as $detail) {
                    $result['purchase_details'][] = new TransactionDetails($detail);
                }                
            }
            
            if(isset($jsonResponse['confirmation_details'])){                
                foreach ($jsonResponse['confirmation_details'] as $detail) {
                    $result['confirmation_details'][] = new TransactionDetails($detail);
                }                
            }
            
            if(isset($jsonResponse['cancellation_details'])){                
                foreach ($jsonResponse['confirmation_details'] as $detail) {
                    $result['cancellation_details'][] = new TransactionDetails($detail);
                }                
            }
            
            if(isset($jsonResponse['refund_details'])){                
                foreach ($jsonResponse['refund_details'] as $detail) {
                    $result['refund_details'][] = new TransactionDetails($detail);
                }                
            }
            
            return [$result, false];
            
        }else{  
            return [false, (new PaymentsResponsesError($jsonResponse))];        
        }
        
    }
    
    public function сallbacks($test = ''){
        
        $entityBody = file_get_contents('php://input');
        
        if(!empty($test)){
            $entityBody = $test;
        }
        
        $json = [];
        
        try {
            $json = json_decode($entityBody, true);
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
        
        return new \Payment\RozetkaPay\Model\PaymentsResponsesSuccess($json);
            
    }    

    private function sendRequest($path, $method = 'GET', $data = array(), $headers = array(), $useToken = true) {
        
        $data_ = $data;
        $url = self::urlBase . $path;

        $method = strtoupper($method);

        $headers = $this->headers;

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);

        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_USERAGENT => 'rozetkapay-php-sdk',
        ));

        switch ($method) {
                        
            case 'POST':
                $data = json_encode($data);
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case 'PUT':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
                break;
            case 'DELETE':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
                break;
            default:
                if (!empty($data)) {
                    $url .= '?' . http_build_query($data);
                }
        }

        $response = curl_exec($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $headerCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $responseBody = substr($response, $header_size);
        $responseHeaders = substr($response, 0, $header_size);
        $ip = curl_getinfo($curl, CURLINFO_PRIMARY_IP);
        $curlErrors = curl_error($curl);

        curl_close($curl);
        
        $jsonResponse = [];
        
        try {
            $jsonResponse = json_decode($responseBody, true);
        } catch (\Exception $exc) {
            echo $exc->getTraceAsString();
        }

        $retval = new \stdClass();
        $retval->request = new \stdClass();
        
        $retval->request->url = $url;
        $retval->request->headers = $headers;
        $retval->request->data = $data_;
        $retval->data = $jsonResponse;
        $retval->http_code = $headerCode;
        $retval->headers = $responseHeaders;
        $retval->ip = $ip;
        $retval->curlErrors = $curlErrors;
        $retval->method = $method . ':' . $url;
        $retval->timestamp = date('Y-m-d h:i:sP');
        
        $this->debug = $retval;
        
        return [$jsonResponse, $headerCode];
    }

}
