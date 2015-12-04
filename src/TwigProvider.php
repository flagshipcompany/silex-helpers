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
            $this->trackUrlFilter($twig);

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

                $unitsString = (($numberOfUnits > 1) ? $numberOfUnits : 'a');
                $plural = (($numberOfUnits > 1) ? 's' : '');

                if ($unitsString === 'a' && $val == 'hour') {
                    $unitsString = 'an';
                }

                if (!$isFuture) {
                    return ($val == 'second') ? 'a few seconds ago' : $unitsString.' '.$val.$plural.' ago';
                }

                return ($val == 'second') ? 'in a few seconds' : 'in '.$unitsString.' '.$val.$plural;
            }

        });

        $twig->addFilter($filter);
    }

    protected function trackUrlFilter($twig)
    {
        $filter = new \Twig_SimpleFilter('trackUrl', function (array $courierAndNbr) {

            $courierId = $courierAndNbr[0];
            $trackingNumber = $courierAndNbr[1];
            $trackingUrl = '';

            if (!$trackingNumber) {
                return $trackingUrl;
            }

            switch ($courierId) {
                case 5:
                    $trackingUrl = 'https://eshiponline.purolator.com/ShipOnline/Public/Track/TrackingDetails.aspx?pup=Y&pin='.$trackingNumber.'&lang=E';
                    break;
                case 2:
                    $trackingUrl = 'http://wwwapps.ups.com/WebTracking/track?HTMLVersion=5.0&loc=en_CA&Requester=UPSHome&trackNums='.$trackingNumber.'&track.x=Track';
                    break;
                case 4:
                    $trackingUrl = 'http://www.fedex.com/Tracking?ascend_header=1&clienttype=dotcomreg&track=y&cntry_code=ca_english&language=english&tracknumbers='.$trackingNumber.'&action=1&language=null&cntry_code=ca_english';
                    break;
                default:
                    $trackingUrl = '';
            }

            return $trackingUrl;
        });

        $twig->addFilter($filter);
    }
}
