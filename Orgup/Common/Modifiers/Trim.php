<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 18.08.11
 * Time: 15:08
 * To change this template use File | Settings | File Templates.
 */
 
namespace Orgup\Common\Modifiers;
use \Orgup\Common\Modifier;

class Trim extends Modifier
{
    public $length;
    public $type;

    protected function createResult()
    {
        $this->object->setOriginalPropertyWhichCantRewrite($this->reflectedProperty(), $this->value, false);

        if(is_array($this->value) || $this->type == 'array')
        {
            $array = array_diff($this->value, array(''));

            if($this->length)
            {
                $array = array_splice($array, (int) $this->length);
            }

            return $array;
        }

        $string = trim($this->value);

        if($this->length)
        {
            $string = mb_substr($string, 0, $this->length, 'UTF-8');
        }

        if($this->type == "int")
        {
            $string = (int) $string;
        }

        return $string;
    }
}