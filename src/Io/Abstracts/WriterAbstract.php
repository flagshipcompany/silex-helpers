<?php

namespace Flagship\Components\Helpers\Io\Abstracts;

use Flagship\Components\Helpers\Io\Interfaces\Closeable;
use Flagship\Components\Helpers\Io\Interfaces\Flushable;
use Flagship\Components\Helpers\Io\Interfaces\Appendable;
use Flagship\Components\Helpers\Io\Exceptions\IOException;

abstract class WriterAbstract implements Closeable, Flushable, Appendable
{
    protected $resource;

    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    public function append($str, $start = false, $end = false)
    {
        if (!$this->resource) {
            throw new IOException('Resource access has been closed', 500);
        }
    }

    public function close()
    {
        if ($this->resource) {
            $this->flush();
            $this->resource->close();
            $this->resource = null;
        }
    }

    public function flush()
    {
        if (!$this->resource) {
            throw new IOException('Resource access has been closed', 500);
        }

        $this->resource->flush();
    }

    public function write($str, $offset = false, $length = false)
    {
        if (!$this->resource) {
            throw new IOException('Resource access has been closed', 500);
        }

        $this->resource->write($str, $offset, $length);
    }
}
