<?php

namespace Flagship\Components\Helpers\Io;

class FileWriter
{
    protected $writer;

    public function __construct($path)
    {
        $dir = str_replace(basename($path), '', $path);

        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!file_exists($path)) {
            touch($path);
        }

        $this->writer = new OutputStreamWriter(new FileOutputStream($path));
    }
}
