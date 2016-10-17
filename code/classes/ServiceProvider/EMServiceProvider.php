<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 09.10.16
 * Time: 22:49
 */

namespace IWG\ServiceProvider;


use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

class EMServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['em'] = function() use ($app) {
            // Create a simple "default" Doctrine ORM configuration for Annotations
            $config = Setup::createAnnotationMetadataConfiguration(
                [ __DIR__ . "/../Model"  ],
                $app['em.devMode']
            );
            // obtaining the entity manager
            return EntityManager::create($app['db'], $config);
        };
    }
}