<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 12.10.11
 * Time: 12:52
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Common\Modifiers;
use \Orgup\Common\Modifier;

class Replace extends Modifier {

    public $regexp;
    public $replace = '';

    protected function createResult()
    {
        $this->object->setOriginalPropertyWhichCantRewrite($this->reflectedProperty(), $this->value, false);
        
        if($this->regexp == 'f|quotes')
        {
            return preg_replace("/(\")([^\"].*){0,}(\")/", "«\\2»", $this->value);
        }

        if($this->regexp == "f|tag")
        {
            return strip_tags($this->value);
        }

        if($this->regexp == "f|html")
        {
            return htmlentities($this->value);
        }

        return preg_replace($this->regexp, $this->replace, $this->value);
    }
}
