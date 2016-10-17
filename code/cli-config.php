<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 07.10.16
 * Time: 21:17
 */
require_once 'bootstrap.php';
return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($entityManager);
