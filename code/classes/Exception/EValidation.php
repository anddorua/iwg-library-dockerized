<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 14.10.16
 * Time: 12:22
 */

namespace IWG\Exception;


use Exception;

class EValidation extends \Exception
{
    private $validationErrors;

    public function __construct($message, $code, $validationErrors)
    {
        parent::__construct($message, $code);
        $this->validationErrors = $validationErrors;
    }

    public function getValidationErrors()
    {
        return $this->validationErrors;
    }
}