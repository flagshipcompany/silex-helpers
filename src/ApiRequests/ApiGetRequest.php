<?php

namespace Flagship\Components\Helpers\ApiRequests;

class ApiGetRequest
{
    protected $app;
    protected $apiUrl;
    protected $uri;

    public function __construct($app)
    {
        $this->app = $app;
        $this->apiUrl = $app['api.url'];
    }

    public function request($uri, $data)
    {
        $headers = $this->app['auth.apiheaders_service']->generateHeaders();
        $location = $this->apiUrl.$uri.'?'.$this->createQuery($data);

        return $this->doRequest($location, $headers);
    }

    protected function doRequest($location, $headers)
    {
        $curl = curl_init($location);

        curl_setopt($curl, CURLOPT_VERBOSE, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl, CURLOPT_TIMEOUT, 8);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        $resp = curl_exec($curl);

        curl_close($curl);

        return $resp;
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
