<?php
namespace Flagship\Components\Helpers\Database\Migrations\Configs;

class Configuration
{
    public static $default = [
        'migrations.path' => './../../../../../../var/migrations',
        'db.options' => [
            'driver'    => 'pdo_mysql',
            'host'      => 'localhost',
            'dbname'    => 'smartship',
            'user'      => 'root',
            'password'  => '',
            'charset'   => 'utf8',
        ],
        'env' => 'dev',
    ];
    
}