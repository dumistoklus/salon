<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 04.11.11
 * Time: 19:19
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Common\Modifiers\Validator;

class Url extends Validator {

   protected static function isValid()
   {
       return filter_var(static::$value, FILTER_VALIDATE_URL);
   }
}


