<?php

namespace Flagship\Components\Helpers\Io;

use Flagship\Components\Helpers\Io\Abstracts\OutputStreamAbstract;
use Flagship\Components\Helpers\Io\Exceptions\IOException;

class FileOutputStream extends OutputStreamAbstract
{
    public function __construct($filename)
    {
        $this->stream = fopen($filename, 'w');

        if (!$this->stream) {
            throw new IOException('File not found or not readable', 500);
        }

        return $this;
    }

    public function close()
    {
        fclose($this->stream);
    }

    public function flush()
    {
        fflush($this->stream);
    }

    public function write($str, $offset = false, $length = false)
    {
        $offset = $offset ?: 0;
        $length = $length ?: strlen($str);

        $src = substr($str, $offset, $length);

        if (!fwrite($this->stream, $src)) {
            throw new IOException('File Output Failed', 500);
        }
    }
}
