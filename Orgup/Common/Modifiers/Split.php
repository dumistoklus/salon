<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 26.11.11
 * Time: 16:13
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Common\Modifiers;

use \Orgup\Common\Modifier;

class Split extends Modifier
{

    public $from;
    public $concat = ' ';
    public $fields;

    protected function createResult()
    {
        $result = '';

        $this->fields = $this->getFields();

        $fieldsCount = sizeof($this->fields);

        for($i = 0; $i < $fieldsCount; $i++)
        {
            $Route = $this->getRoute();
            $Route->set_object($this->object);
            $Route->Modify($this->reflectedProperty());
            $Route->suppress_warnings = $this->suppress_warnings;
            $Route->name = $this->fields[$i];
            $routeResult = trim($Route->getResult());
            $result .= $routeResult;

            $this->object->setCustomProperty($this->fields[$i], $routeResult, true);

            if($i < $fieldsCount - 1)
            {
                $result .= $this->concat;
            }
        }

        return $result;
    }

    private function getFields()
    {
        $fields = explode('|' , $this->fields);
        return $fields;
    }

    public function getRoute()
    {
        if($this->from == 'Post')
        {
            return new Post();
        }
        else
        {
            return new Get();
        }
    }
}
