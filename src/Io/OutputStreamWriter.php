<?php

namespace Flagship\Components\Helpers\Io;

use Flagship\Components\Helpers\Io\Abstracts\WriterAbstract;
use Flagship\Components\Helpers\Io\Abstracts\OutputStreamAbstract;

class OutputStreamWriter extends WriterAbstract
{
    public function __construct(OutputStreamAbstract $resource)
    {
        parent::__construct($resource);
    }
}
