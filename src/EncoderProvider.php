<?php
namespace Flagship\Components\Helpers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class EncoderProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['flagship.helpers.base64encode'] = $app->protect(function ($data) {
            return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
        });

        $app['flagship.helpers.base64decode'] = $app->protect(function ($data) {
            return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
        });
    }
}
