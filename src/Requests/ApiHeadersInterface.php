<?php

namespace Flagship\Components\Helpers\Requests;

interface ApiHeadersInterface
{
    public function generateAdminHeaders($isJson = true, $companyId = null);

    public function generateHeaders($isJson = true);
}
