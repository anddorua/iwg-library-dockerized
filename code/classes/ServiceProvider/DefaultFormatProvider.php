<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 12.10.16
 * Time: 1:11
 */

namespace IWG\ServiceProvider;


use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class DefaultFormatProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['default_format'] = $app->protect(function(Request $request) use ($app) {
            $support = $app['default_format.support'];
            $accept = array_map(function($elem){
                list($base, $specific) = explode('/', $elem);
                return $specific;
            }, $request->getAcceptableContentTypes());
            $using = array_intersect($accept, $support);
            $using[] = $support[0];
            return array_shift($using);
        });
    }

}