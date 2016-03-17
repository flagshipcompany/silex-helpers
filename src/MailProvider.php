<?php

namespace Flagship\Components\Helpers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class MailProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['swiftmailer.transport'] = function ($app) {
            $transport = new \Swift_Transport_EsmtpTransport(
                $app['swiftmailer.transport.buffer'],
                array($app['swiftmailer.transport.authhandler']),
                $app['swiftmailer.transport.eventdispatcher']
            );

            $options = $app['swiftmailer.options'] = array_replace(array(
                'host' => 'localhost',
                'port' => 25,
                'username' => '',
                'password' => '',
                'encryption' => null,
                'auth_mode' => null,
            ), $app['swiftmailer.options']);

            $transport->setHost($options['host']);
            $transport->setPort($options['port']);
            $transport->setEncryption($options['encryption']);
            $transport->setUsername($options['username']);
            $transport->setPassword($options['password']);
            $transport->setAuthMode($options['auth_mode']);

            if (null !== $app['swiftmailer.sender_address']) {
                $transport->registerPlugin(new \Swift_Plugins_ImpersonatePlugin($app['swiftmailer.sender_address']));
            }

            if (isset($app['swiftmailer.delivery_address'])) {
                $transport->registerPlugin(new \Swift_Plugins_RedirectingPlugin($app['swiftmailer.delivery_address']));
            }

            return $transport;
        };
    }
}
