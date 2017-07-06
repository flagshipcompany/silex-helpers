<?php

namespace Flagship\Components\Helpers\Env;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Dotenv\Dotenv;

class ServiceProvider implements ServiceProviderInterface
{
    protected $envPath = __DIR__.'/../../../../../.env';

    public function register(Container $app)
    {
        if (file_exists($this->envPath)) {
            $this->loadEnvFile(new Dotenv());
        }

        require_once 'globals.php';
    }

    protected function loadEnvFile(Dotenv $dotenv)
    {
        $dotenv->load($this->envPath);
    }
}
