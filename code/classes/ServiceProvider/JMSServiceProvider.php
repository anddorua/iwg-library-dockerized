<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 14.10.16
 * Time: 1:27
 */

namespace IWG\ServiceProvider;


use Pimple\Container;
use Pimple\ServiceProviderInterface;
use JMS\Serializer;

class JMSServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['jms'] = function() use ($app) {
            $serializer = Serializer\SerializerBuilder::create()
                ->addMetadataDir($app['jms.metadata-dir'])
                ->build();
            return $serializer;
        };
    }

}