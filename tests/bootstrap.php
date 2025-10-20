<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$routingContractsPath = __DIR__ . '/../../maduser-argon-routing/src/Contracts';

if (is_dir($routingContractsPath)) {
    $contracts = [
        'RouteInterface.php' => \Maduser\Argon\Routing\Contracts\RouteInterface::class,
        'RouteContextInterface.php' => \Maduser\Argon\Routing\Contracts\RouteContextInterface::class,
        'RequestHandlerResolverInterface.php' => \Maduser\Argon\Routing\Contracts\RequestHandlerResolverInterface::class,
    ];

    foreach ($contracts as $file => $interface) {
        if (!interface_exists($interface) && file_exists($routingContractsPath . '/' . $file)) {
            require_once $routingContractsPath . '/' . $file;
        }
    }
}
