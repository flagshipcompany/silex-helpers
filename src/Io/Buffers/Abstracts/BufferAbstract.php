<?php

namespace Flagship\Components\Helpers\Io\Buffers\Abstracts;

abstract class BufferAbstract
{
    protected $buffer;
    protected $mark;
    protected $position;
    protected $capacity;

    /**
     * Returns this buffer's capacity.
     *
     * @return int
     */
    public function capacity()
    {
        return $this->capcity;
    }

    /**
     * Clears this buffer.
     *
     * The position is set to zero, the mark is discarded.
     *
     * @return Buffer
     */
    public function clear()
    {
        $this->position = 0;
        unset($this->mark);
        unset($this->buffer);

        return $this;
    }

    /**
     * Flips this buffer.
     *
     * the position is set to zero.
     * If the mark is defined then it is discarded
     *
     * @return Buffer
     */
    public function flip()
    {
        unset($this->position);
        unset($this->mark);

        return $this;
    }

    /**
     * Sets this buffer's mark at its position.
     *
     * @return Buffer
     */
    public function mark()
    {
        $this->mark = $this->position;

        return $this;
    }

    /**
     * Returns this buffer's position.
     *
     * @return int
     */
    public function position()
    {
        return $this->position;
    }

    public function hasRemaining()
    {
        return $this->remaining() > 0;
    }

    /**
     * Returns the number of elements between the current position and the capacity.
     *
     * @return int
     */
    public function remaining()
    {
        return ($this->capacity - $this->position);
    }

    /**
     * Resets this buffer's position to the previously-marked position.
     *
     * Invoking this method neither changes nor discards the mark's value.
     *
     * @return Buffer
     */
    public function reset()
    {
        if (!isset($this->mark)) {
            throw new IOException('Invalid mark exception');
        }

        $this->position = $this->mark;

        return $this;
    }

    /**
     * Rewinds this buffer.
     *
     * The position is set to zero and the mark is discarded
     *
     * @return Buffer
     */
    public function rewind()
    {
        $this->position = 0;
        unset($this->mark);

        return $this;
    }

    /**
     * Tells whether or not this buffer is read-only.
     *
     * @return bool
     */
    abstract public function isReadonly();
}
