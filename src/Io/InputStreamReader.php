<?php

namespace Flagship\Components\Helpers\Io;

use Flagship\Components\Helpers\Io\Abstracts\ReaderAbstract;
use Flagship\Components\Helpers\Io\Abstracts\InputStreamAbstract;

class InputStreamReader extends ReaderAbstract
{
    public function __construct(InputStreamAbstract $resource)
    {
        $this->resource = $resource;
    }

    public function markSupported()
    {
        return $this->resource->markSupported();
    }
}
