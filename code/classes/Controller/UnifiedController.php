<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 14.10.16
 * Time: 18:13
 */

namespace IWG\Controller;

use Doctrine\ORM\EntityManager;
use IWG\Exception\EOperationDeny;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use IWG\Exception\EModel;
use IWG\Exception\EValidation;


abstract class UnifiedController implements ControllerProviderInterface
{

    protected $entityClass;

    public function getListUnified(Application $app, Request $request)
    {
        /** @var EntityManager $em */
        $em = $app['em'];
        $categories = $em->getRepository($this->entityClass)->findAll();
        $format = $app['default_format']($request);
        return new Response($app['jms']->serialize($categories, $format), 200, [
            'Content-Type' => $request->getMimeType($format),
        ]);
    }

    /**
     * @param Application $app
     * @param $id integer
     * @param $request Request
     * @return Response
     * @throws EModel
     */
    public function getSingleUnified(Application $app, Request $request, $id)
    {
        /** @var EntityManager $em */
        $em = $app['em'];
        $entity = self::findSingleEntity($em, $this->entityClass, $id);

        $format = $app['default_format']($request);
        return new Response($app['jms']->serialize($entity, $format), 200, [
            'Content-Type' => $request->getMimeType($format),
        ]);
    }

    public function postEntityUnified(Application $app, Request $request)
    {
        /** @var \IWG\Model\Category $category */
        $entity = $app['jms']->deserialize(
            $request->getContent(),
            $this->entityClass,
            $app['request_format']($request)
        );

        self::validateEntityCreated($app, $entity);

        /** @var EntityManager $em */
        $em = $app['em'];
        $em->persist($entity);
        $em->flush();
        $format = $app['default_format']($request);
        return new Response(null, 201, [
            'Content-Type' => $request->getMimeType($format),
            'Location' => $this->getEntityLocation($app, $entity),
        ]);
    }

    public function putEntityUnified(Application $app, Request $request, $id)
    {
        /** @var EntityManager $em */
        $em = $app['em'];
        $entity = self::findSingleEntity($em, $this->entityClass, $id);

        /** @var \IWG\Model\Category $entityNew */
        $entityNew = $app['jms']->deserialize(
            $request->getContent(),
            $this->entityClass,
            $app['request_format']($request)
        );

        self::validateEntityCreated($app, $entityNew);
        $this->assignOwnFields($entity, $entityNew);

        $em->flush($entity);
        $format = $app['default_format']($request);
        return new Response(null, 204, [
            'Content-Type' => $request->getMimeType($format),
        ]);
    }

    /**
     * @param $author object
     * @return void
     * @throws EOperationDeny
     */
    protected abstract function testCanDelete($author);

    public function deleteSingleUnified(Application $app, Request $request, $id)
    {
        /** @var EntityManager $em */
        $em = $app['em'];
        $entity = self::findSingleEntity($em, $this->entityClass, $id);

        $this->testCanDelete($entity);
        $em->remove($entity);
        $em->flush();

        $format = $app['default_format']($request);
        return new Response($app['jms']->serialize($entity, $format), 200, [
            'Content-Type' => $request->getMimeType($format),
        ]);
    }

    protected static function validateEntityCreated(Application $app, $entity)
    {
        $errors = $app['validator']->validate($entity, null, ['creation']);
        if (count($errors) != 0) {
            throw new EValidation("Validation errors", 400, $errors);
        }
    }

    /**
     * @param EntityManager $em
     * @param string $entityClass
     * @param int $id
     * @return mixed
     * @throws EModel
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public static function findSingleEntity(EntityManager $em, $entityClass, $id)
    {
        $entity = $em->find($entityClass, $id);
        if ($entity === null) {
            throw new EModel("$entityClass $id not found", 404);
        }
        return $entity;
    }

    /**
     * @param Application $app
     * @param object $entity
     * @return string
     */
    abstract protected function getEntityLocation(Application $app, $entity);

    /**
     * @param object $entityDest
     * @param object $entitySrc
     * @return mixed
     */
    abstract protected function assignOwnFields($entityDest, $entitySrc);
}