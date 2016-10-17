<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 06.10.16
 * Time: 22:31
 */

namespace IWG\Model;


use Doctrine\Common\Collections\ArrayCollection;

interface BookInterface
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
     * @param $year int
     * @return void
     */
    public function setYearOfIssue($year);

    /**
     * @return int
     */
    public function getYearOfIssue();

    /**
     * @param $author AuthorInterface
     * @return void
     */
    public function assignAuthor($author);

    /**
     * @return AuthorInterface[]
     */
    public function getAuthors();

    /**
     * @param $category CategoryInterface
     * @return void
     */
    public function setCategory($category);

    /**
     * @return CategoryInterface
     */
    public function getCategory();

    public function detachAuthors();

    public function assignOwnFields(BookInterface $src);

}