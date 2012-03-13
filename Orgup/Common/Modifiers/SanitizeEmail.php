<?php
namespace Orgup\Common\Modifiers;
use Orgup\Common\Modifier;

class SanitizeEmail extends Modifier
{
    protected function createResult()
    {
        $this->object->setOriginalPropertyWhichCantRewrite($this->reflectedProperty(), $this->value, false);
        return filter_var($this->value, FILTER_SANITIZE_EMAIL);
    }
}