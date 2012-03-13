<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 22.08.11
 * Time: 17:00
 * To change this template use File | Settings | File Templates.
 */

namespace Orgup\Common;
use \Orgup\Application\Exception\Modifiers\ModNotInited;

abstract class Mod {

    private $errors = array();
    private $notCreated = true;

    private $usedProperties = array();
    /**
     * @var OriginalProperties
     */
    private $originalProperties;
    
    function __construct()
    {
        $this->originalProperties;
    }

    private function checkOriginalProperties()
    {
        if($this->originalProperties == null)
        {
            $this->originalProperties = new OriginalProperties();
        }
    }

    public function setUsedProperties(\ReflectionProperty &$property, $value)
    {
        $this->checkOriginalProperties();
        $this->usedProperties[$property->getName()] = $value;
        $this->originalProperties->add($property, $value);
    }

    public function setOriginalPropertyWhichCantRewrite(\ReflectionProperty &$property, $value)
    {
        $this->checkOriginalProperties();
        $this->originalProperties->add($property, $value, false);
    }

    public function setCustomProperty($property, $value, $canRewrite)
    {
        $this->checkOriginalProperties();
        $this->originalProperties->addCustom($property, $value, $canRewrite);
    }

    public function usedProperties()
    {
        return array_keys($this->usedProperties);
    }

    public function deleteFromUsedProperties($property)
    {
        unset($this->usedProperties[$property]);
    }

    public function set_error($mod, $error)
    {
        $this->errors[$mod][] = $error;
    }

    public function removeFromError($mod, $error)
    {
        if(isset($this->errors[$mod]))
        {
            $id = array_search($error, $this->errors[$mod]);
            unset($this->errors[$mod], $id);
        }
    }

    public function removeFromValidateError($error)
    {
        $this->removeFromError('Validate', $error);
    }

    public function get_errors($mod)
    {
        if(isset($this->errors[$mod]))
            return $this->errors[$mod];
        else return array();
    }

    public function getAllErrors()
    {
        return $this->errors;
    }

    public function addErrors(array $errors)
    {
        $this->errors = array_merge_recursive($this->errors, $errors);
    }

    public function noError()
    {
        return empty($this->errors);
    }

    public function noErrorsOn($type)
    {
        $errors = $this->get_errors($type);
        return empty($errors);
    }

    public function validateErrors()
    {
        return $this->get_errors('Validate');
    }

    public function initModifiers()
    {
        if($this->notCreated)
        {
            $this->notCreated = false;
            $Modifier = new ModifyObject($this);
            $Modifier->create();
            $Modifier = null;

        }
    }

    public function asArray()
    {
        return $this->usedProperties;
    }

    public function originalsArray()
    {
        $this->checkOriginalProperties();
        return $this->originalProperties->properties();
    }
}

class OriginalProperties
{
    private $properties = array();

    private $cantRewrite = array();

    public function addCustom($propertyName, $value, $canRewrite = true)
    {

        if(!isset($this->cantRewrite[$propertyName]))
        {
            $this->properties[$propertyName] = $value;
        }

        if(!$canRewrite)
        {
            $this->cantRewrite[$propertyName] = false;
        }
    }

    public function add(\ReflectionProperty &$property, $value, $canRewrite = true)
    {
        $this->addCustom($property->getName(), $value, $canRewrite);
    }

    public function properties()
    {
        return $this->properties;
    }
}
