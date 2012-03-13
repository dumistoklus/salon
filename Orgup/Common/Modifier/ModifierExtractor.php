<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 23.09.11
 * Time: 15:24
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Common\Modifier;
use \Orgup\Application\Exception\Modifiers\ModifierNotExists;

class ModifierExtractor {

    private $modifierDeclaration;

    function __construct($declaration)
    {
        preg_match('/(\w+)\((.*)\)/',$declaration, $this->modifierDeclaration);


        if(!$this->validModifier())
        {
            throw new ModifierNotExists();
        }
    }

    public function getClass()
    {
        return $this->modifierDeclaration[1];
    }

    public function getParamsAndValues()
    {
        if(preg_match_all('/(?:(\w+)[\s]{0,}=[\s]{0,}"([^"]+)")/', $this->modifierDeclaration[2], $matches))
        {
            $paramsCount = sizeof($matches[1]);
            $params = array();

            for($i = 0; $i < $paramsCount; $i++)
            {
                $params[$matches[1][$i]] = $matches[2][$i];
            }

            return $params;
        }
        
        return array();

    }

    private function validModifier()
    {
        /*
         * $modifierDeclaration представляет собой распарсенную строку, наподобие Class(name="sdfsdf")
         * поэтому если строка правильная, $modifierDeclaration должен содержать 3 элемента
         */
        return sizeof($this->modifierDeclaration) == 3;
    }
}
