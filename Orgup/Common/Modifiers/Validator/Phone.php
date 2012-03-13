<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 24.11.11
 * Time: 17:07
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Common\Modifiers\Validator;

class Phone extends Validator
{
   protected static function isValid()
   {
       return preg_match('/^(\+7|7|8)[\d]{10}$/', self::$value);
   }
}
