<?php

namespace Orgup\Common\Modifiers;

use \Orgup\Common\Modifier;
use Orgup\Application\Registry;

class Post extends Modifier
{
    public $name;
    public $required = true;
    public $is_array = false;
    public $is_checkbox = false;
    public $default = '';

    protected $errorType = 'Post';


    protected function createResult()
    {
        if($this->name == null)
        {
            $this->name = $this->property_name;
        }

        $routingValue = $this->routeParam($this->name);
        
        if($this->is_checkbox == true)
        {
            return $routingValue === 'on';
        }
        if($this->is_array && is_array($routingValue ))
        {
            return $routingValue;
        }
        else if($routingValue !== null && !$this->is_array && !is_array($routingValue)) {
            return $routingValue;
        }
        else if(!$this->required)
        {
            $this->object->set_error($this->errorType, $this->name);

            $method = explode('|', $this->default);
            if(count($method) > 1 && $method[0] == 'm')
            {
                return eval('return '.$method[1].';');
            }
            else
            {
                return $this->default;
            }
        }
        else
        {
            $this->object->set_error($this->errorType, $this->name);

            return $this->value;
        }
    }

    protected function routeParam($name)
    {
        return Registry::instance()->get('Routing')->route_post($name);
    }
}