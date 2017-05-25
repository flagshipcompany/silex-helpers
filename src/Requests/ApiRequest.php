<?php

namespace Flagship\Components\Helpers\Requests;

class ApiRequest
{
    protected $app;
    protected $apiUrl;
    protected $uri;
    protected $latestHttpCode;

    public function __construct($app)
    {
        $this->app = $app;
        $this->apiUrl = $app['api.url'];
    }

    public function get($uri, $data)
    {
        if ($data) {
            $uri .= $this->createQuery($data);
        }

        return $this->doRequest($uri, null, 'GET');
    }

    public function post($uri, $data)
    {
        return $this->doRequest($uri, $data, 'POST');
    }

    public function delete($uri, $data)
    {
        return $this->doRequest($uri, $data, 'DELETE');
    }

    public function patch($uri, $data)
    {
        return $this->doRequest($uri, $data, 'PATCH');
    }

    public function put($uri, $data)
    {
        return $this->doRequest($uri, $data, 'PUT');
    }

    public function getLatestHttpCode()
    {
        return $this->latestHttpCode;
    }

    public function isSuccessful()
    {
        return $this->latestHttpCode > 199 && $this->latestHttpCode < 300;
    }

    protected function doRequest($uri, $data, $method)
    {
        $headers = $this->app['helpers.apiheaders_service']->getHeaders();

        $hasFiles = $this->hasFiles($data ?? []);

        if (is_array($data) && !$hasFiles) {
            $headers[] = 'Content-Type:application/json';
            $data = json_encode($data);
        }

        if ($hasFiles) {
            $headers[] = 'Content-Type:multipart/form-data';
        }

        $location = $this->apiUrl.$uri;

        $curl = $this->createCurlHandler($location, $headers, $method);

        if ($method != 'GET') {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        $resp = curl_exec($curl);

        $this->latestHttpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if (curl_getinfo($curl, CURLINFO_CONTENT_TYPE) == 'application/json') {
            curl_close($curl);

            return json_decode($resp, true);
        }

        if ($resp === false) {
            curl_close($curl);

            return ['errors' => 'Looks like the server is unreacheable or is timing out'];
        }
        $errorStr = 'Invalid API response. Expected application/json, received '.curl_getinfo($curl, CURLINFO_CONTENT_TYPE);

        curl_close($curl);

        return ['errors' => $errorStr];
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
        curl_setopt($curl, CURLOPT_TIMEOUT, 12);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        return $curl;
    }

    protected function createQuery($data)
    {
        $pieces = [];
        $filtered = array_filter($data, function ($item) {
            return $item !== '' && $item !== null;
        });

        foreach ($filtered as $name => $value) {
            $pieces = array_merge($pieces, $this->createPiece($name, $value));
        }

        return '?'.implode('&', $pieces);
    }

    protected function createPiece($name, $value)
    {
        $piece = [];
        //if its a single value
        if (!is_array($value)) {
            $piece[] = $name.'='.urlencode($value);

            return $piece;
        }
        //if there are multiple values (collection)
        foreach ($value as $k => $v) {
            $piece[] = $name.'[]='.urlencode($v);
        }

        return $piece;
    }

    protected function hasFiles($data)
    {
        if (!is_array($data)) {
            return;
        }

        $objects = array_filter($data, function ($item) {
            return is_object($item);
        });

        return count($objects) > 0;
    }
}
