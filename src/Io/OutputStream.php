<?php

namespace Flagship\Components\Helpers\Io;

use Flagship\Components\Helpers\Io\Abstracts\OutputStreamAbstract;
use Flagship\Components\Helpers\Io\Interfaces\Closeable;
use Flagship\Components\Helpers\Io\Interfaces\Flushable;
use Flagship\Components\Helpers\Io\Exceptions\IOException;

class OutputStream extends OutputStreamAbstract implements Closeable, Flushable
{
    public function __construct()
    {
        $this->stream = fopen('php://output', 'w');
    }

    public function close()
    {
        if (!$this->stream) {
            $this->flush();
            fclose($this->stream);
            $this->stream = null;
        }
    }

    public function flush()
    {
        if (!$this->stream) {
            throw new IOException('Resource access has been closed', 500);
        }

        flush();
    }

    public function write($str, $offset = false, $length = false)
    {
        if (!$this->stream) {
            throw new IOException('Resource access has been closed', 500);
        }

        $str = substr($str, 0, $length ?: strlen($str));

        fwrite($this->stream, $str);
    }
}
