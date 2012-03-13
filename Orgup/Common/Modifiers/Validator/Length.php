<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 28.09.11
 * Time: 13:26
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Common\Modifiers\Validator;

class Length extends Validator
{

    protected static function isValid()
    {
        $length = 0;

        if(self::$value === null) return false;

        if(is_string(self::$value))
        {
            $length = mb_strlen(self::$value);
        }
        else
        {
           return false;
        }

        $values = explode('|', self::$between);
        $count = count($values);

        if($count == 1)
        {
            return $length > (int) $values[0];
        }
        else if($count == 2)
        {
            if(trim($values[1]) == 'inf') {
                $values[1] = $length + 1;
            }

            return (((int)$values[0] < $length) && ($length < (int)$values[1]));
        }

        return false;

    }
}