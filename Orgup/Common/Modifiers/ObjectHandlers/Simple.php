<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 23.09.11
 * Time: 16:55
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Common\Modifiers\ObjectHandlers;
use \Orgup\Common\ModifyObject;
use \Orgup\Common\Modifier\ModifierExtractor;
use \Orgup\Application\Exception\Modifiers\ModifierNotExists;

class Simple
{
    private $object;
    /**
     * @var \Orgup\Common\ModifiersCollection
     */
    private $Modifiers;

    function __construct(&$object, \Orgup\Common\ModifiersCollection $Modifiers)
    {
        $this->object = $object;
        $this->Modifiers = $Modifiers;
    }

    public function create()
    {
        while($this->Modifiers->valid() && $ModifierSettings = $this->Modifiers->next())
        {
            $Modifier = $ModifierSettings->class;
            
            $Modifier->set_object($this->object);

            foreach($ModifierSettings->params as $paramName => $paramValue)
            {
                if($paramValue == 'true')
                {
                    $paramValue = true;
                }
                else if($paramValue == 'false')
                {
                    $paramValue = false;
                }

                $Modifier->{$paramName} = $paramValue;
            }
            
            $reflectedProperty = $ModifierSettings->propery;

            $reflectedProperty->setAccessible(true);

            $Modifier->Modify($reflectedProperty);

            $reflectedProperty->setValue($this->object, $Modifier->getResult());

        }

        $this->Modifiers = null;
        $this->object = null;
    }
}
