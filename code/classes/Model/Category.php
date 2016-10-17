<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 08.10.16
 * Time: 0:47
 */

namespace IWG\Model;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;

/**
 * Class Category
 * @package Model
 * @Entity
 * @Table(name="categories")
 */
class Category implements CategoryInterface, OwnFieldsAwareInterface
{

    /**
     * @var int
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $name;

    /**
     * @OneToMany(targetEntity="Book", mappedBy="category")
     * @var BookInterface[]
     */
    protected $books = null;

    public static function getOwnFieldList()
    {
        return ['name'];
    }


    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('name', new Assert\NotBlank([
            'groups' => ['creation'],
        ]));
        $metadata->addPropertyConstraint('id', new Assert\Blank([
            'groups' => ['creation'],
        ]));
    }

    /**
     * Category constructor.
     */
    public function __construct()
    {
        $this->books = new ArrayCollection();
    }

    /**
     * @param BookInterface $book
     */
    public function assignedToBook($book)
    {
        $this->books[] = $book;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return ArrayCollection|BookInterface[]
     */
    public function getBooks()
    {
        return $this->books;
    }

    public function assignOwnFields(CategoryInterface $src)
    {
        $this->setName($src->getName());
    }


}