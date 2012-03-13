<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 12.10.11
 * Time: 13:13
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Common\Modifiers\Validator;

class Match extends Validator
{
    protected static function isValid()
    {
        return preg_match(static::$between, static::$value);
    }
}
