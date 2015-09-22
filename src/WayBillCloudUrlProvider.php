<?php

namespace Flagship\Components\Helpers;

use Flagship\Components\Helpers\Io\AWSFileOutputStream;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class WayBillCloudUrlProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['flagship.helpers.cloudUrl'] = $app->protect(function ($filename) use ($app) {

            $conf = $app['aws.s3']['waybill'];

            return (new AWSFileOutputStream(
                $conf['credentials'],
                $conf['region'],
                $conf['bucket'],
                $conf['acl']
            ))->getRemoteUrl($filename);
        });
    }
}
