<?php

namespace Flagship\Components\Helpers\Io\Abstracts;

use Flagship\Components\Helpers\Io\Interfaces\Closeable;
use Flagship\Components\Helpers\Io\Interfaces\Readable;
use Flagship\Components\Helpers\Io\Exceptions\IOException;
use Flagship\Components\Helpers\Io\Buffers\CharBuffer;

abstract class ReaderAbstract implements Closeable, Readable
{
    protected $resource;
    protected $position = 0;

    public function __construct(InputStreamAbstract $resource)
    {
        $this->resource = $resource;
    }

    public function close()
    {
        $this->resource->close();
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
        $this->resource->mark();
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
        return $this->resource->read($cb, $length, $offset);
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
        return $this->resource->reset();
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
        return $this->resource->skip($offset, $whence);
    }
}
