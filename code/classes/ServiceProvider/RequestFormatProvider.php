<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 14.10.16
 * Time: 11:51
 */

namespace IWG\ServiceProvider;


use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class RequestFormatProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['request_format'] = $app->protect(function(Request $request) use ($app) {
            list($commonContentType, $requestFormat) = explode('/',$request->getContentType());
            $requestFormat = !empty($requestFormat) ? $requestFormat : $request->getContentType();
            return $requestFormat;
        });
    }
}