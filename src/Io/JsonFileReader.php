<?php

namespace Flagship\Components\Helpers\Io;

class JsonFileReader
{
    protected $reader;

    public function __construct($filename)
    {
        $this->reader = new InputStreamReader(new FileInputStream($filename));
    }

    /**
     * @param bool $asAssoc output as array
     *
     * @return arry
     */
    public function getJson($asAssoc = true)
    {
        $str = $this->reader->read();

        if (!$str) {
            return $str;
        }

        $json = json_decode($str, $asAssoc);

        if (!$json) {
            throw new \Exception('Invalid JSON string');
        }

        return $json;
    }
}
