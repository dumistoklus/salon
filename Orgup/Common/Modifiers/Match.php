<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 12.10.11
 * Time: 12:58
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Common\Modifiers;
use \Orgup\Common\Modifier;

class Match extends Modifier {

    public $regexp;
    public $matches = false;

    protected function createResult()
    {
        $result = (bool) preg_match($this->regexp,$this->value, $matches);

        if(!$result)
        {
            $this->addValidateError();
        }

        if($this->matches)
        {
            unset($matches[0]);
            return $matches;
        }

        return $this->value;
    }
}
