<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 14.10.16
 * Time: 16:53
 */

namespace IWG\Model;


interface OwnFieldsAwareInterface
{
    /**
     * @return array
     */
    public static function getOwnFieldList();
}