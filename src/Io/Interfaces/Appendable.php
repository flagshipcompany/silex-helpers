<?php

namespace Flagship\Components\Helpers\Io\Interfaces;

interface Appendable
{
    public function append($str, $start = false, $end = false);
}
