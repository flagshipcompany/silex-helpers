<?php

namespace Flagship\Components\Helpers\Requests;

class ApiRequestMulti extends ApiRequest
{
    protected $curls = [];
    protected $results = [];
    protected $statuses = [];

    public function get($uri, $data)
    {
        if ($data) {
            $uri .= $this->createQuery($data);
        }

        return $this->addRequest($uri, null, 'GET');
    }

    public function post($uri, $data)
    {
        return $this->addRequest($uri, $data, 'POST');
    }

    public function delete($uri, $data)
    {
        return $this->addRequest($uri, $data, 'DELETE');
    }

    public function patch($uri, $data)
    {
        return $this->addRequest($uri, $data, 'PATCH');
    }

    public function put($uri, $data)
    {
        return $this->addRequest($uri, $data, 'PUT');
    }

    public function getLatestHttpCode()
    {
        $latest = end($this->statuses);
        reset($this->statuses);

        return $latest;
    }

    public function isSuccessful()
    {
        $isSuccessful = true;
        foreach ($this->statuses as $stat) {
            $isSuccessful &= $stat > 199 && $stat < 300;
        }

        return (bool) $isSuccessful;
    }

    public function addRequest($uri, $data, $method)
    {
        $this->curls[] = $this->createSingleCurlHandler($uri, $data, $method);
    }

    public function execute()
    {
        $mh = curl_multi_init();

        foreach ($this->curls as $ch) {
            curl_multi_add_handle($mh, $ch);
        }

        $running = true;
        do {
            curl_multi_exec($mh, $running);
        } while ($running);

        foreach ($this->curls as $c) {
            $contentType = curl_getinfo($c, CURLINFO_CONTENT_TYPE);
            $this->results[] = $contentType == 'application/json' ? json_decode(curl_multi_getcontent($c), true) : curl_multi_getcontent($c);
            $this->statuses[] = curl_getinfo($c, CURLINFO_HTTP_CODE);
            curl_multi_remove_handle($mh, $c);
            curl_close($c);
        }

        curl_multi_close($mh);
    }

    public function getResults()
    {
        return $this->results;
    }

    protected function createSingleCurlHandler($uri, $data, $method)
    {
        $headers = $this->app['helpers.apiheaders_service']->getHeaders();

        if (is_array($data)) {
            $headers[] = 'Content-Type:application/json';
            $data = json_encode($data);
        }

        $location = $this->apiUrl.$uri;

        $curl = parent::createCurlHandler($location, $headers, $method);

        if ($method != 'GET') {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        return $curl;
    }
}
