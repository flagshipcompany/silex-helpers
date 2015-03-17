<?php
namespace Flagship\Components\Helpers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class TwigProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['twig'] = $app->extend('twig', function ($twig, $app) {
            $this->assetFunction($twig, $app);
            $this->classsetFunction($twig);
            $this->timeagoFilter($twig);

            return $twig;
        });
    }

    protected function assetFunction($twig, $app)
    {
        $function = new \Twig_SimpleFunction('asset', function ($asset) use ($app) {
                return $app['request_stack']->getMasterRequest()->getBasepath().'/'.$asset;
        });
        $twig->addFunction($function);
    }

    protected function classsetFunction($twig)
    {
        $function = new \Twig_SimpleFunction('classset', function ($arg) {
            return implode(' ', array_keys(array_filter($arg)));
        });
        $twig->addFunction($function);
    }

    protected function timeagoFilter($twig)
    {
        $filter = new \Twig_SimpleFilter('timeago', function ($datetime) {

            $time = time() - strtotime($datetime);
            $isFuture = $time < 0;
            $time = abs($time);

            $units = [
                31536000 => 'year',
                2592000 => 'month',
                604800 => 'week',
                86400 => 'day',
                3600 => 'hour',
                60 => 'minute',
                1 => 'second',
            ];

            foreach ($units as $unit => $val) {
                if ($time < $unit) {
                    continue;
                }

                $numberOfUnits = floor($time / $unit);

                $unitsString = (($numberOfUnits>1) ? $numberOfUnits : 'a');
                $plural = (($numberOfUnits>1) ? 's' : '');

                if (!$isFuture) {
                    return ($val == 'second') ? 'a few seconds ago' : $unitsString.' '.$val.$plural.' ago';
                }

                return ($val == 'second') ? 'in a few seconds' : 'in '.$unitsString.' '.$val.$plural;
            }

        });

        $twig->addFilter($filter);
    }
}
