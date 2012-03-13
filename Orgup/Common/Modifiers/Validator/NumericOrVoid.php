<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ilya
 * Date: 26.12.11
 * Time: 12:30
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Common\Modifiers\Validator;
use Orgup\Common\Modifiers\Validator\Numeric;

class NumericOrVoid extends  Numeric
{
    protected static function isValid()
    {
        if(static::$value == '') return true;
        else return Numeric::isValid();
    }
}
