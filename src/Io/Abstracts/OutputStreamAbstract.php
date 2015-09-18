<?php

namespace Flagship\Components\Helpers\Io\Abstracts;

use Flagship\Components\Helpers\Io\Interfaces\Closable;
use Flagship\Components\Helpers\Io\Interfaces\Flushable;

abstract class OutputStreamAbstract implements Closable, Flushable
{
    protected $stream;

    abstract public function close();

    abstract public function flush();

    abstract public function write($str, $offset = false, $length = false);
}
