<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 06.10.16
 * Time: 22:28
 */

namespace IWG\Model;


use Doctrine\Common\Collections\ArrayCollection;

interface CategoryInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @param $name string
     * @return void
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param $book BookInterface
     * @return void
     */
    public function assignedToBook($book);

    /**
     * @return ArrayCollection
     */
    public function getBooks();

    /**
     * @param CategoryInterface $src
     * @return mixed
     */
    public function assignOwnFields(CategoryInterface $src);

}