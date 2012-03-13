<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 22.09.11
 * Time: 12:00
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Common\Modifiers\Validator;

class Equals extends Validator{

    protected static function isValid()
    {
        $values = explode('|', static::$between);

        $result = false;

        foreach($values as &$value)
        {
            if($value == 'true')
            {
                $value = true;
            }
            if($value == 'false')
            {
                $value = false;
            }

            if(defined($value))
            {
                $value = constant($value);
            }

            if(preg_match('/^{{(\w+)}}$/', $value, $matches))
            {
                $value = $matches[1];
                $properties = static::$Mod->asArray();
                $value = $properties[$value];
            }
            
            $result = $result || ($value == static::$value);
        }

        return $result;
    }
}
