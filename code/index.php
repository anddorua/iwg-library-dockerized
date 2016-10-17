<?php
/**
 * Library app
 * here is story:
 * REST API приложение “Библиотека".
 *  1. Разработать REST API.
 *      Требования:
 *  1.1. Сущность Автор.  Поля: имя, фамилия, год рождения.
 *  1.2. Сущность Категория. Поля: название.
 *  1.3. Сущность Книга. Поля: имя, дата издания. Связь с сущностями Автор и Категория.
 *  1.4. Приложение должно использовать Silex, а также PostgreSQL.
 *  1.5. Реализовать полноценный CRUD над всеми сущностями.
 * 2. Научить приложение возвращать правильный HTTP код.
 * 3. Шаблоны проектирования.
 * *4. Реализовать авторизацию (один из способов авторизации OAuth 2.0).
 * **5. Завернуть приложение в docker. Для сервера использовать nginx + php-fpm.
 * ***6. Развернуть приложение в облаке.
 *
 * see https://iwaygroup.slack.com/messages/general/
 *
 * Created by PhpStorm.
 * User: andrey
 * Date: 09.10.16
 * Time: 18:01
 */

require __DIR__ . '/vendor/autoload.php';

/** @var $app Silex\Application */
$app = new Silex\Application();
$app['debug'] = true;

$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__.'/development.log',
));

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => require('config/dbal.php'),
));

$app->register(new \IWG\ServiceProvider\EMServiceProvider(), [
    'em.devMode' => true,
]);

$app->register(new \Silex\Provider\SerializerServiceProvider());

$app->register(new Silex\Provider\VarDumperServiceProvider());

$app->register(new Silex\Provider\ValidatorServiceProvider());

$app->register(new \IWG\ServiceProvider\DefaultFormatProvider(), [
    'default_format.support' => ['json', 'xml'],
]);

$app->register(new \IWG\ServiceProvider\RequestFormatProvider());

$app->register(new \IWG\ServiceProvider\JMSServiceProvider(), [
    'jms.metadata-dir' => __DIR__ . "/config/metadata",
]);

$app->mount('categories', new \IWG\Controller\Category());
$app->mount('authors', new \IWG\Controller\Author());
$app->mount('books', new \IWG\Controller\Book());
$app->get('/', function() use ($app) {
    return $app->redirect('/front/index.html');
});

$app->error(function (\IWG\Exception\EModel $e, \Symfony\Component\HttpFoundation\Request $request, $code) use ($app) {
    $format = $app['default_format']($request);
    return new \Symfony\Component\HttpFoundation\Response($app['serializer']->serialize(
        [
            'message' => $e->getMessage(),
            'type' => get_class($e),
        ], $format), $e->getCode(), [
            'Content-Type' => $request->getMimeType($format),
        ]
    );
});

$app->error(function (\IWG\Exception\EValidation $e, \Symfony\Component\HttpFoundation\Request $request, $code) use ($app) {
    $format = $app['default_format']($request);
    return new \Symfony\Component\HttpFoundation\Response($app['serializer']->serialize(
        [
            'message' => $e->getMessage(),
            'type' => get_class($e),
            'errors' => $e->getValidationErrors(),
        ], $format), $e->getCode(), [
            'Content-Type' => $request->getMimeType($format),
        ]
    );
});

$app->error(function (\IWG\Exception\EOperationDeny $e, \Symfony\Component\HttpFoundation\Request $request, $code) use ($app) {
    $format = $app['default_format']($request);
    return new \Symfony\Component\HttpFoundation\Response($app['serializer']->serialize(
        [
            'message' => $e->getMessage(),
            'type' => get_class($e),
        ], $format), $e->getCode(), [
            'Content-Type' => $request->getMimeType($format),
        ]
    );
});

$app->run();