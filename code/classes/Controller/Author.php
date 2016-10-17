<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 13.10.16
 * Time: 0:10
 */

namespace IWG\Controller;


use Doctrine\ORM\EntityManager;
use Silex\ControllerCollection;
use Silex\Application;
use IWG\Exception\EModel;


class Author extends UnifiedController
{

    public function __construct()
    {
        $this->entityClass = 'IWG\\Model\\Author';
    }

    protected function getEntityLocation(Application $app, $entity)
    {
        return $app['url_generator']->generate('author', ['id' => $entity->getId()]);
    }

    protected function assignOwnFields($entityDest, $entitySrc)
    {
        $entityDest->assignOwnFields($entitySrc);
    }

    public function connect(Application $app)
    {
        /** @var ControllerCollection $controllers  */
        $controllers = $app['controllers_factory'];
        $controllers->get('/', [$this, 'getListUnified'])->bind('author-list');
        $controllers->get('/{id}', [$this, 'getSingleUnified'])->bind('author')->assert('id', '\d+');
        $controllers->post('/', [$this, 'postEntityUnified']);
        $controllers->put('/{id}', [$this, 'putEntityUnified'])->assert('id', '\d+');
        $controllers->delete('/{id}', [$this, 'deleteSingleUnified'])->assert('id', '\d+');
        return $controllers;
    }

    protected function testCanDelete($author)
    {
        if ($author->getBooks()->count() > 0) {
            throw new \IWG\Exception\EOperationDeny("Author linked to books.", 409);
        }
    }

    /**
     * @param EntityManager $em
     * @param int $id
     * @return \IWG\Model\Author
     * @throws EModel
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public static function findAuthor(EntityManager $em, $id)
    {
        return self::findSingleEntity($em, 'IWG\\Model\\Author', $id);
    }
}