<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 13.10.16
 * Time: 1:16
 */

namespace IWG\Controller;

use Doctrine\ORM\EntityManager;
use IWG\Model\Author;
use Silex\Api\ControllerProviderInterface;
use Silex\ControllerCollection;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use IWG\Exception\EModel;
use IWG\Exception\EValidation;

class Book extends UnifiedController
{

    public function __construct()
    {
        $this->entityClass = 'IWG\\Model\\Book';
    }

    public function connect(Application $app)
    {
        /** @var ControllerCollection $controllers  */
        $controllers = $app['controllers_factory'];
        $controllers->get('/', [$this, 'getListUnified'])->bind('book-list');
        $controllers->get('/{id}', [$this, 'getSingleUnified'])->bind('book')->assert('id', '\d+');
        $controllers->post('/', [$this, 'postEntityUnified']);
        $controllers->put('/{id}', [$this, 'putEntityUnified'])->assert('id', '\d+');
        $controllers->put('/{id}/category', [$this, 'putCategory'])->assert('id', '\d+');
        $controllers->put('/{id}/authors', [$this, 'putAuthors'])->assert('id', '\d+');
        $controllers->delete('/{id}', [$this, 'deleteSingleUnified'])->assert('id', '\d+');
        return $controllers;
    }

    protected function testCanDelete($book)
    {
        return;
    }

    protected function getEntityLocation(Application $app, $entity)
    {
        return $app['url_generator']->generate('book', ['id' => $entity->getId()]);
    }

    protected function assignOwnFields($entityDest, $entitySrc)
    {
        $entityDest->assignOwnFields($entitySrc);
    }

    public function getList(Application $app, Request $request)
    {
        /** @var EntityManager $em */
        $em = $app['em'];
        $categories = $em->getRepository('IWG\\Model\\Book')->findAll();
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
    public function getSingle(Application $app, Request $request, $id)
    {
        /** @var EntityManager $em */
        $em = $app['em'];
        $book = $em->find('IWG\\Model\\Book', $id);
        if ($book === null) {
            throw new EModel("Book $id not found", 404);
        }
        $format = $app['default_format']($request);
        return new Response($app['jms']->serialize($book, $format), 200, [
            'Content-Type' => $request->getMimeType($format),
        ]);
    }

    public function postEntity(Application $app, Request $request)
    {
        /** @var \IWG\Model\Book $book */
        $book = $app['jms']->deserialize(
            $request->getContent(),
            'IWG\\Model\\Book',
            $app['request_format']($request)
        );

        $errors = $app['validator']->validate($book, null, ['creation']);
        if (count($errors) != 0) {
            throw new EValidation("Validation errors", 400, $errors);
        }

        /** @var EntityManager $em */
        $em = $app['em'];
        $em->persist($book);
        $em->flush();
        return new Response(null, 201, [
            'Content-Type' => $request->getMimeType($app['default_format']($request)),
            'Location' => $app['url_generator']->generate('book', ['id' => $book->getId()]),
        ]);
    }

    public function putCategory(Application $app, Request $request, $id)
    {
        /** @var \IWG\Model\Category $categoryToFind */
        $categoryToFind = $app['jms']->deserialize(
            $request->getContent(),
            'IWG\\Model\\Category',
            $app['request_format']($request)
        );

        /** @var EntityManager $em */
        $em = $app['em'];
        /** @var \IWG\Model\Category $category */
        $category = \IWG\Controller\Category::findCategory($em, $categoryToFind->getId());

        $book = $this->findBook($em, $id);

        $book->setCategory($category);
        $em->persist($book);
        $em->flush();

        $format = $app['default_format']($request);
        return new Response(null, 204, [
            'Content-Type' => $request->getMimeType($format),
        ]);
    }

    public function putAuthors(Application $app, Request $request, $id)
    {
        /** @var \IWG\Model\Author[] $authorsToFind */
        $authorsToFind = $app['jms']->deserialize(
            $request->getContent(),
            'Doctrine\\Common\\Collections\\ArrayCollection<IWG\\Model\\Author>',
            $app['request_format']($request)
        );

        /** @var EntityManager $em */
        $em = $app['em'];

        $authors = array_map(function(Author $authorToFind) use ($em) {
            return \IWG\Controller\Author::findAuthor($em, $authorToFind->getId());
        }, $authorsToFind);

        $book = $this->findBook($em, $id);
        $book->detachAuthors();

        array_walk($authors, function($author) use ($book, $em) {
            $book->assignAuthor($author);
            $em->persist($author);
        });
        $em->persist($book);
        $em->flush();

        $format = $app['default_format']($request);
        return new Response(null, 204, [
            'Content-Type' => $request->getMimeType($format),
        ]);
    }


    /**
     * @param EntityManager $em
     * @param integer $id
     * @return \IWG\Model\Book
     * @throws EModel
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    private function findBook(EntityManager $em, $id)
    {
        $book = $em->find('IWG\\Model\\Book', $id);
        if ($book === null) {
            throw new EModel("Book $id not found", 404);
        }
        return $book;
    }

}