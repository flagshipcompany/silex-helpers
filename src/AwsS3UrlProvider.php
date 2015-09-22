<?php

namespace Flagship\Components\Helpers;

use Flagship\Components\Helpers\Io\AWSFileOutputStream;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class AwsS3UrlProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['flagship.helpers.cloudUrl'] = $app->protect(function ($bucket, $filename) use ($app) {

            $conf = $app['aws.s3'][$bucket];

            return (new AWSFileOutputStream(
                $conf['credentials'],
                $conf['region'],
                $conf['bucket'],
                $conf['acl']
            ))->getRemoteUrl($filename);
        });
    }
}
