<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 22.11.11
 * Time: 15:12
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Common\Modifiers\Validator;
class Word extends Validator
{
   protected static function isValid()
   {
       return preg_match('/^[\w\s\-]+$/u', self::$value);
   }
}
