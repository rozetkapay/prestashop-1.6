<?php

namespace Payment\RozetkaPay\HttpClient;

use Payment\RozetkaPay\Exception\HttpClientException;

class HttpCurl implements Payment\RozetkaPay\HttpClient\ClientInterface {

    /**
     * @var array
     */
    private $options = [
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HEADER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 60,
        CURLOPT_USERAGENT => 'rozetkapay-php-sdk',
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_SSL_VERIFYPEER => 1,
        CURLOPT_TIMEOUT => 60
    ];

    /**
     * @param string $method
     * @param string $url
     * @param array $headers
     * @param array $params
     * @return string
     * @throws Exception\HttpClientException
     */
    public function request($method, $url, $headers = [], $params = []) {
        $method = strtoupper($method);
        if (!$this->curlEnabled())
            throw new HttpClientException('Curl not enabled.');
        if (empty($url))
            throw new HttpClientException('The url is empty.');

        $ch = curl_init($url);
        foreach ($this->options as $option => $value) {
            curl_setopt($ch, $option, $value);
        }
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if ($params) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
        $response = curl_exec($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpStatus != 200)
            throw new HttpClientException(sprintf('Status is: %s', $httpStatus));
        curl_close($ch);
        return trim($response);
    }

    /**
     * @return bool
     */
    private function curlEnabled() {
        return function_exists('curl_init');
    }

}
