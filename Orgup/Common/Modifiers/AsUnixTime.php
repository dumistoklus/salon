<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 22.11.11
 * Time: 15:07
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Common\Modifiers;
use \Orgup\Common\Modifier;

class AsUnixTime extends Modifier
{
    protected function createResult()
    {
        $this->object->setOriginalPropertyWhichCantRewrite($this->reflectedProperty(), $this->value, false);

        $time = strtotime($this->value);

        if($time === false)
        {
            $this->addValidateError();
        }

        return $time;
    }
}
