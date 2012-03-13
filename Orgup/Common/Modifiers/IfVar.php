<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 18.10.11
 * Time: 15:59
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Common\Modifiers;
use \Orgup\Common\Modifier;
use \Orgup\Application\ClassLoader;

class IfVar extends Modifier
{
    public $name;
    public $equals;
    public $use;
    public $importFrom;
    /**
     * @var \Orgup\Common\Mod
     */
    private $ReplaceableObject;

    protected function createResult()
    {
        $this->object->deleteFromUsedProperties($this->property_name);

        if($this->true())
        {
            $this->ReplaceableObject = $this->loadClass();

            $properties = $this->ReplaceableObject->usedProperties();

            foreach($properties as $property)
            {
                $this->object->{$property} = $this->ReplaceableObject->{$property};
                $this->object->setUsedProperties($property);
            }

            $this->object->addErrors($this->ReplaceableObject->getAllErrors());

            return $this->ReplaceableObject;
        }

        return null;
    }

    private function true()
    {
        if($this->equals != null)
        {
            return Equals::true($this->object->{$this->name}, $this->equals);
        }

        return false;
    }

    private function loadClass()
    {
        $Object = null;

        if($this->importFrom != null)
        {
            $Object = new $this->use;
        }
        else
        {
            $Class = $this->getReplaceableObjectClassName();
            $Object = new $Class;
        }

        $Object->initModifiers();

        return $Object;
    }

    private function getReplaceableObjectClassName()
    {
        $className = get_class($this->object);

        $paths = explode('\\', $className);
        array_pop($paths);

        $namespace = implode('\\', $paths).'\\';

        return $namespace.$this->use;
    }
}

class Equals
{
    public static function true($value, $expected)
    {
        return $value == $expected;
    }
}
