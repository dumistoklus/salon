<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 14.07.11
 * Time: 17:24
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Common;

class UserAuthDataValidator {
    public static function valid_login($login)
    {
        return ( preg_match( '/^'.self::loginRegexp().'$/', $login ));
    }

    public static function loginRegexp()
    {
        return '[a-z]{1}[a-z0-9]{3,}';
    }

    public static function valid_password($password)
    {
         return ( mb_strlen( $password ) > 5 );
    }

    public static function passwordRegexp()
    {
        return '.{5,}';
    }

    public static function valid_email($email)
    {
        return (filter_var($email, FILTER_VALIDATE_EMAIL) && mb_strlen($email) > 6);
    }
}
