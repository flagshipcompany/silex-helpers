<?php

namespace Flagship\Components\Helpers\Io;

class JsonFileWriter extends FileWriter
{
    protected $writer;

    public function __construct($filename)
    {
        parent::__construct($filename);
    }

    /**
     * @param bool $asAssoc output as array
     */
    public function writeJson(array $json)
    {
        $this->writer->write(json_encode($json));
    }
}
