<?php

namespace Flagship\Components\Helpers\ApiRequests;

class ApiRequest
{
    protected $app;
    protected $apiUrl;
    protected $uri;

    public function __construct($app)
    {
        $this->app = $app;
        $this->apiUrl = $app['api.url'];
    }

    public function get($uri, $data, $isAdmin = false)
    {
        $uri .= '?'.$this->createQuery($data);

        return $this->doRequest($uri, [], 'GET', $isAdmin);
    }

    public function post($uri, $data, $isAdmin = false, $isJson = true)
    {
        return $this->doRequest($uri, $data, 'POST', $isAdmin, $isJson);
    }

    public function delete($uri, $data, $isAdmin = false, $isJson = false)
    {
        return $this->doRequest($uri, $data, 'DELETE', $isAdmin, $isJson);
    }

    protected function doRequest($uri, $data, $method, $isAdmin = false, $isJson = false)
    {
        $headers = $isAdmin
            ? $this->app['auth.apiheaders_service']->generateAdminHeaders(false, $isJson)
            : $this->app['auth.apiheaders_service']->generateHeaders(false, $isJson);

        $location = $this->apiUrl.$uri;

        $curl = $this->createCurlHandler($location, $headers, $method);

        if ($method != 'GET') {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        $resp = curl_exec($curl);

        curl_close($curl);

        return $resp;
    }

    protected function createCurlHandler($location, $headers, $method)
    {
        $curl = curl_init($location);

        curl_setopt($curl, CURLOPT_VERBOSE, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if ($method != 'GET') {
            curl_setopt($curl, CURLOPT_POST, true);
        }

        if (!in_array($method, ['GET', 'POST'])) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        }

        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl, CURLOPT_TIMEOUT, 8);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        return $curl;
    }

    protected function createQuery($data)
    {
        $pieces = [];
        $filtered = array_filter($data, function ($item) {
            return !empty($item);
        });

        foreach ($filtered as $name => $value) {
            $pieces[] = $name.'='.urlencode($value);
        }

        return implode('&', $pieces);
    }
}
