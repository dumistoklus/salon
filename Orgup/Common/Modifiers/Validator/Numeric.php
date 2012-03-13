<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 22.09.11
 * Time: 11:27
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Common\Modifiers\Validator;

class Numeric extends Validator{

    protected static function isValid()
    {
        $min = (int) static::$value - 1;
        $max = (int) static::$value + 1;

        if(static::$between)
        {
            $between = explode('|', static::$between);

            if(count($between) == 2)
            {
                list( $min, $max ) = $between;

                if(defined($min))
                {
                    $min = constant($min);
                }

                if(defined($max))
                {
                    $max = constant($max);
                }

                if($max == 'inf')
                {
                    $max = self::$value + 1;
                }
            }
            else
            {
                if(defined($min))
                {
                    $min = constant($min);
                }
                else
                {
                    $min = (int) $between[0];
                }
            }
        }

        if(is_numeric(static::$value))
        {
            return $min <= static::$value && static::$value <= $max;
        }

        if(is_array(static::$value))
        {
            $between = true;

            foreach(static::$value as $value)
            {
                $between = $between && is_numeric($value);

                if(static::$between)
                    $between = $between && ($min <= static::$value && $value <= $max);
            }

            return $between;
        }

        return false;
    }
}
