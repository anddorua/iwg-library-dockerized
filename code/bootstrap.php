<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 07.10.16
 * Time: 21:10
 */

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

require_once 'vendor/autoload.php';

$isDevMode = true;
$config = Setup::createAnnotationMetadataConfiguration([__DIR__ . '/classes/Model'], $isDevMode);
$entityManager = EntityManager::create(require('config/dbal.php'), $config);
