<?php

namespace Flagship\Components\Helpers\Io;

use Flagship\Components\Helpers\Io\Abstracts\InputStreamAbstract;
use Flagship\Components\Helpers\Io\Exceptions\IOException;

class FileInputStream extends InputStreamAbstract
{
    public function __construct($filename)
    {
        $this->resource = fopen($filename, 'r');

        if (!$this->resource) {
            throw new IOException('File not found or not readable');
        }

        return $this;
    }

    /**
     * Tells whether this stream supports the mark() operation.
     *
     * @return bool
     */
    public function markSupported()
    {
        return true;
    }
}
