<?php

namespace Flagship\Components\Helpers\Io\Buffers;

use Flagship\Components\Helpers\Io\Buffers\Abstracts\BufferAbstract;

class CharBuffer extends BufferAbstract
{
    public function __construct($str = null)
    {
        return $this->wrap($str);
    }

    public function __toString()
    {
        return $this->buffer;
    }

    /**
     * @param string $str
     * @param int    $length
     * @param int    $offset
     *
     * @return CharBuffer
     */
    public function append($str, $length = false, $offset = false)
    {
        $offset = $offset ?: 0;
        $length = $length ?: strlen($str);

        $src = substr($str, $offset, $length);

        $this->buffer = $this->buffer.$src;
        $this->capacity = strlen($this->buffer);
        $this->position = $this->capacity - 1;

        return $this;
    }

    /**
     * @param int $index
     *
     * @return string
     */
    public function charAt($index)
    {
        if ($index >= $this->capacity) {
            throw new \OutOfRangeException('Index out of range of buffer');
        }

        return $this->buffer{$index};
    }

    /**
     * Tells whether or not this buffer is read-only.
     *
     * @return bool
     */
    public function isReadOnly()
    {
        return false;
    }

    /**
     * @param string $str
     * @param int    $position
     *
     * @return CharBuffer
     */
    public function wrap($str, $position = 0)
    {
        $this->buffer = $str;
        $this->capacity = strlen($this->buffer);
        $this->position = $position;

        return $this;
    }

    /**
     * copy string into buffer, starting at current buffer's position.
     *
     * @param string $str
     * @param int    $length The number of chars to be read from the given array; must be non-negative and no larger than array.length - offset
     * @param int    $offset The offset within the array of the first char to be read; must be non-negative and no larger than array.length
     *
     * @return CharBuffer
     */
    public function put($str, $length = false, $offset = false)
    {
        $offset = $offset ?: 0;
        $length = $length ?: strlen($str);

        $src = substr($str, $offset, $length);

        $remaining = '';

        if ($this->hasRemaining() && $this->remaining() > $length) {
            $remaining = substr($this->buffer, $this->position + $length - 1);
        }

        $this->buffer = substr_replace($this->buffer, $src, $this->position).$remaining;

        $this->capacity = strlen($this->buffer);
        $this->position = $this->position + $length - 1;

        return $this;
    }
}
