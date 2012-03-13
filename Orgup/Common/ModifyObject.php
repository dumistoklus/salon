<?php
namespace Orgup\Common;
use \Orgup\Common\Modifiers;
use \Orgup\Common\Modifier\ModifierExtractor;
use \Orgup\Application\Exception\Modifiers\ModifierNotExists;
use \Orgup\Application\Exception\UnsupportedOperation;
use \Orgup\Common\Modifiers\ObjectHandlers\Simple;

abstract class Modifier
{
    protected $value;
    /**
     * @var \Orgup\Common\Mod
     */
    protected $object;
    protected $property_name;

    public $suppress_warnings = false;
    /**
     * @var \ReflectionProperty
     */
    private $reflectedProperty;

	abstract protected function createResult();

	public function Modify(\ReflectionProperty &$reflectedProperty)
    {
        $this->value = $reflectedProperty->getValue($this->object);
        $this->property_name = $reflectedProperty->getName();
        $this->reflectedProperty = $reflectedProperty;
    }

    public function set_object(\Orgup\Common\Mod &$object)
    {
        $this->object = $object;
    }
    /**
     * @return \ReflectionProperty
     */
    public function & reflectedProperty()
    {
        return $this->reflectedProperty;
    }

    public function getResult()
    {
        $value = $this->createResult();

        $this->object->setUsedProperties($this->reflectedProperty, $value);

        return $value;
    }

    protected function addError($type)
    {
        if(!$this->suppress_warnings)
        {
            $this->object->set_error($type, $this->property_name);
        }
    }

    protected function addValidateError()
    {
        $this->addError('Validate');
    }
}

class ModifyObject
{
    const ModifiersNamespace = 'Orgup\Common\Modifiers\\';

    private static $commentCache = array();

	protected $object;

	final function __construct(&$object)
	{
		$this->object = $object;
        $this->isMod();
	}

    public function create()
    {
        $reflectedObject = new \ReflectionObject($this->object);

        $Modifiers = new ModifiersCollection();

        foreach($reflectedObject->getProperties() as $property)
        {
            try
            {
                $reflectedProperty = new \ReflectionProperty($this->object, $property->name);
                $comment =  $reflectedProperty->getDocComment();

                if($comment)
                {
                    $Modifier = $this->searchModifiersDeclarations($comment);

                    foreach($Modifier as $ModifierDeclaration)
                    {
                        $Extractor = new ModifierExtractor($ModifierDeclaration);

                        $ModifierClass = self::ModifiersNamespace.$Extractor->getClass();

                        if(class_exists($ModifierClass))
                        {
                            $ModifierSettings = new ModifierSettings();

                            $ModifierSettings->class = new $ModifierClass;
                            $ModifierSettings->params = $Extractor->getParamsAndValues();
                            $ModifierSettings->propery = $reflectedProperty;

                            $Modifiers[] = $ModifierSettings;

                        }
                    }
                }
            }
            catch(ModifierNotExists $e)
            {
                continue;
            }
        }
        $ModifierType = new Simple($this->object, $Modifiers);
        $ModifierType->create();
        return $this->object;
    }

    private function isMod()
    {
        if (!($this->object instanceof Mod))
            throw new UnsupportedOperation('Not instance of Mod');

        return true;
    }

    protected function searchModifiersDeclarations($comment)
    {
        $comment_hash = base64_encode($comment);

        if(isset(self::$commentCache[$comment])) return self::$commentCache[$comment_hash];

        preg_match_all('/@(.*)/', $comment, $declarations);
        return self::$commentCache[$comment_hash] = $declarations[1];
    }
}

class ModifiersCollection implements \ArrayAccess, \Iterator
{
    private $ModifiersSettings;
    private $offset = 0;

    public function offsetExists($offset)
    {
        return isset($this->ModifiersSettings[$offset]);
    }
    /**
     * @param $offset
     * @return \Orgup\Common\ModifierSettings
     */
    public function offsetGet($offset)
    {
        return $this->ModifiersSettings[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if($offset !== null)
        {
            $this->ModifiersSettings[$offset] = $value;
        }
        else
        {
            $this->ModifiersSettings[] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->ModifiersSettings[$offset]);
    }
    /**
     * @throws \OutOfRangeException
     * @return \Orgup\Common\ModifierSettings
     */
    public function current()
    {
        if($this->offsetExists($this->offset))
        {
            return $this->offsetGet($this->offset);
        }
        else throw new \OutOfRangeException();
    }
    /**
     * @return \Orgup\Common\ModifierSettings
     */
    public function next()
    {
        $return = $this->current();
        $this->offset += 1;

        return $return;
    }

    public function key()
    {
        return $this->offset;
    }

    public function valid()
    {
        return $this->offsetExists($this->offset);
    }

    public function rewind()
    {
        $this->offset = 0;
    }
}

class ModifierSettings
{
    /**
     * @var \Orgup\Common\Modifier
     */
    public $class;
    /**
     * @var array
     */
    public $params;
    /**
     * @var \ReflectionProperty
     */
    public $propery;
}