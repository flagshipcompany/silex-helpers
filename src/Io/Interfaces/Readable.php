<?php

namespace Flagship\Components\Helpers\Io\Interfaces;

use Flagship\Components\Helpers\Io\Buffers\CharBuffer;

interface Readable
{
    /**
     * Attempts to read characters into the specified character buffer..
     *
     * @param Flagship\Components\Helpers\Io\Interfaces\CharBuffer $cb
     *
     * @throws Flagship\Components\Helpers\Io\IOException - if an I/O error occurs
     */
    public function read(CharBuffer $cb = null, $offset = false, $length = false);
}
