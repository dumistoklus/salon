<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 26.11.11
 * Time: 16:53
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Common\Modifiers;

use \Orgup\Common\Modifier;

class DateTime extends Modifier
{
    public $format = 'd-m-Y H:i:s';
    public $to;

    protected function createResult()
    {
        $Date = \DateTime::createFromFormat($this->format, $this->value);
        $this->object->setOriginalPropertyWhichCantRewrite($this->reflectedProperty(), $this->value, false);

        if($Date === false)
        {
            $this->addValidateError();
            return $Date;
        }

        if($this->to !== null)
        {
            if($this->to == 'unix')
            {
                return $Date->getTimestamp();
            }

            if($this->to == 'mysql')
            {
                return $Date->format('Y-m-d H:i:s');
            }

            return $Date->format($this->to);
        }

        return $Date;
    }
}
