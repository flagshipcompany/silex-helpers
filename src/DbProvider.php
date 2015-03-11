<?php
namespace Flagship\Components\Helpers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class DbProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['flagship.helpers.pdoHydrateArray'] = $app->protect(function ($rows) {
            $z = [];
            foreach ($rows as $row) {
                foreach ($row as $k => $v) {
                    $z[$k][] = $v;
                }
            }

            array_walk($z, function (&$v, $k) {
                $v = array_unique($v);
                if (count($v) === 1) {
                    $v = $v[0];
                }

            });

            array_walk_recursive($z, function (&$v, $k) {
                $v = is_numeric($v) ? $v+0 : $v;
            });

            return count($z) > 0 ? $z : false;
        });
    }
}
