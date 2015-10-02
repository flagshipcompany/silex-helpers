<?php

namespace Flagship\Components\Helpers\Io;

class JsonFileWriter
{
    protected $writer;

    public function __construct($filename)
    {
        $this->writer = new OutputStreamWriter(new FileOutputStream($filename));
    }

    /**
     * @param bool $asAssoc output as array
     */
    public function writeJson(array $json)
    {
        $this->writer->write(json_encode($json));
    }
}
