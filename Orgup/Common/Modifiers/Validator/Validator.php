<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 22.09.11
 * Time: 11:10
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Common\Modifiers\Validator;
use \Orgup\Common\Modifiers\Validate;
use \Orgup\Common\Mod;

abstract class Validator
{
    protected static $value;
    protected static $between;
    /**
     * @var \Orgup\Common\Mod
     */
    protected static $Mod;

    final static function validate($value, $between, $propertyName, Mod $Mod)
    {
        self::$value = $value;
        self::$between = $between;
        self::$Mod = $Mod;

        self::check($propertyName);
    }

    private final static function check($propertyName)
    {
        if(!static::isValid())
        {
            self::$Mod->set_error('Validate', $propertyName);
        }

        self::$Mod = null;
    }

    protected static function isValid()
    {
        return false;
    }
}
