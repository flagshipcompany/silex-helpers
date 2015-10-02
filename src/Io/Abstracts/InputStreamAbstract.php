<?php

namespace Flagship\Components\Helpers\Io\Abstracts;

use Flagship\Components\Helpers\Io\Interfaces\Closeable;
use Flagship\Components\Helpers\Io\Buffers\CharBuffer;
use Flagship\Components\Helpers\Io\Exceptions\IOException;

abstract class InputStreamAbstract implements Closeable
{
    protected $resource;
    protected $position = 0;

    /**
     * Closes this input stream and releases any system resources associated with the stream.
     *
     * The close method of InputStream does nothing.
     *
     * @throws IOException if an I/O error occurs
     */
    public function close()
    {
        if (fclose($this->resource)) {
            return;
        }

        throw new IOException('Failed to close resource '.(new \ReflectionClass($this))->getShortName());
    }

    /**
     * @return resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Marks the present position in the stream.
     *
     * Subsequent calls to reset() will attempt to reposition the resource to this point
     * Not all input streams support the mark() operation.
     *
     * @throws IOException If the resource does not support mark(), or if some other I/O error occurs
     *
     * @return int position
     */
    public function mark()
    {
        if (!$this->markSupported()) {
            throw new IOException('Resouce '.(new \ReflectionClass($this))->getShortName().' does not support mark operation.');
        }

        $position = ftell($this->resource);

        if ($position === false) {
            throw new IOException('Mark operation failed to read current position from resource '.(new \ReflectionClass($this))->getShortName().'.');
        }

        $this->position = $position;

        return $this->position;
    }

    /**
     * Tells whether this stream supports the mark() operation.
     *
     * The default implementation always returns false. Subclasses should override this method.
     *
     * @return bool
     */
    public function markSupported()
    {
        return false;
    }

    /**
     * @param CharBuffer $cb     the buffer into which the data is read
     * @param int        $offset the start offset in $cb at which the data is written
     * @param int        $length the maximum number of bytes to read
     *
     * @throws IOException if an I/O error occurs
     */
    public function read(CharBuffer $cb = null, $offset = false, $length = false)
    {
        if (!$this->resource) {
            throw new IOException('Resource access has been closed', 500);
        }

        if ($offset) {
            $this->skip($offset);
        }

        $data = $length ? fread($this->resource, $length) : stream_get_contents($this->resource);

        if ($data === false) {
            throw new IOException('Failed to read resource '.(new \ReflectionClass($this))->getShortName());
        }

        if ($cb) {
            $cb->wrap($data);
        }

        return $data;
    }

    /**
     * Resets the stream.
     *
     * If the stream has been marked, then attempt to reposition it at the mark. If the stream has not been marked, then attempt to reset it in some way appropriate to the particular stream, for example by repositioning it to its starting point.
     *
     * @return bool
     */
    public function reset()
    {
        if ($this->position > 0) {
            return $this->mark($this->position);
        }

        return rewind($this->resource);
    }

    /**
     * Skips characters.
     *
     * This method will block until some characters are available, an I/O error occurs, or the end of the stream is reached.
     *
     * @param int $offset The number of characters to skip
     * @param int $whence SEEK_SET|SEEK_CUR|SEEK_END
     *
     * @return bool
     */
    public function skip($offset = 0, $whence = SEEK_SET)
    {
        if ($whence == SEEK_END && $offset > 0) {
            throw new IOException('To move to a position before the end-of-reader-stream, you need to pass a negative value in offset and set whence to SEEK_END. ');
        }

        if (fseek($this->resource, $offset, $whence) > -1) {
            return true;
        }

        throw new IOException('Resouce failed to perform skip operation.');

        return false;
    }
}
