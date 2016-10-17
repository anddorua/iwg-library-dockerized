<?php
/**
 * Model/Author.php
 *
 * Created by PhpStorm.
 * User: andrey
 * Date: 07.10.16
 * Time: 21:40
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
 * Class Author
 * @package Model
 * @Entity
 * @Table(name="authors")
 */
class Author implements AuthorInterface
{
    /**
     * @var int
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $name;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $fName;

    /**
     * @var int
     * @Column(type="integer")
     */
    protected $yearOfBirth;


    /**
     * @ManyToMany(targetEntity="Book", mappedBy="authors")
     * @var BookInterface[]
     */
    protected $books = null;

    /**
     * Author constructor.
     */
    public function __construct()
    {
        $this->books = new ArrayCollection();
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('name', new Assert\NotBlank([
            'groups' => ['creation'],
        ]));
        $metadata->addPropertyConstraint('fName', new Assert\NotBlank([
            'groups' => ['creation'],
        ]));
        $metadata->addPropertyConstraint('yearOfBirth', new Assert\NotBlank([
            'groups' => ['creation'],
        ]));
        $metadata->addPropertyConstraint('yearOfBirth', new Assert\Type([
            'type' => 'integer',
            'groups' => ['creation'],
        ]));
    }


    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setFName($fName)
    {
        $this->fName = $fName;
    }

    public function getFName()
    {
        return $this->fName;
    }

    public function setYearOfBirth($year)
    {
        $this->yearOfBirth = $year;
    }

    public function getYearOfBirth()
    {
        return $this->yearOfBirth;
    }

    public function assignedBook($book)
    {
        $this->books[] = $book;
    }

    public function detachedBook($book)
    {
        $this->books->removeElement($book);
    }

    public function assignOwnFields(AuthorInterface $src)
    {
        $this->setName($src->getName());
        $this->setFName($src->getFName());
        $this->setYearOfBirth($src->getYearOfBirth());
    }

    public function getBooks()
    {
        return $this->books;
    }


}