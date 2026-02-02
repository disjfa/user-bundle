<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $services->defaults()
    ->autowire()
    ->autoconfigure()
    ->private();

    $services->load('Disjfa\\UserBundle\\', '../src/*')
        ->exclude('../src/{DependencyInjection,Entity,Migrations,Tests,Kernel.php}');

    $services
        ->load('Disjfa\\UserBundle\\Controller\\', '../src/Controller')
        ->tag('controller.service_arguments');
};
