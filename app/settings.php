<?php

declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {

    // Global Settings Object
    $containerBuilder->addDefinitions([
        SettingsInterface::class => function () {
            return new Settings([
                'displayErrorDetails' => true, // Should be set to false in production
                'logError'            => true,
                'logErrorDetails'     => true,
                'logger' => [
                    'name' => 'slim-app',
                    'path' => isset($_ENV['docker']) ? 'php://stderr' : __DIR__ . '/../logs/app.log',
                    'level' => Logger::DEBUG,
                ],
                'rrd' => [
                    'path' => __DIR__ . '/../storage' ,
                ],

                'db' => [
                    'path' => __DIR__ . '/../storage/thermometre.db' ,
                ],

                'json' => [
                    'path' => __DIR__ . '/../storage/thermometre.json' ,
                ],

                'tz' => isset($_ENV['TZ']) ? $_ENV['TZ'] : 'UTC',

                'auth_token' => isset($_ENV['THERMOMETRE_TOKEN']) ? $_ENV['THERMOMETRE_TOKEN'] : '',
            ]);
        }
    ]);
};
