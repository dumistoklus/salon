<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 22.09.11
 * Time: 11:09
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Common\Modifiers\Validator;
use \Orgup\Common\UserAuthDataValidator;

class Email extends Validator{

    protected static function isValid()
    {
        return UserAuthDataValidator::valid_email(static::$value);
    }
}
