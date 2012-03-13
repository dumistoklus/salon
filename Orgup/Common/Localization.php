<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 05.10.11
 * Time: 12:44
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Common;

use \Orgup\Application\ConfigLoader;
use \Orgup\Application\Registry;
use \Orgup\Application\Exception\UnsupportedOperation;

class Localization implements \ArrayAccess {

    private $ConfigLoader;
    private $localeArray;

    private $defaultLocaleLoaded;

    function __construct(ConfigLoader $ConfigLoader, array $localeArray, $lang)
    {
        $this->localeArray = $localeArray;

        $this->defaultLocaleLoaded = ($lang == Registry::instance()->User()->getDefaultLocale());

        if(!$this->defaultLocaleLoaded)
        {
            $this->ConfigLoader = $ConfigLoader;
        }
    }

    private function addAnotherLocale()
    {
        $this->defaultLocaleLoaded = true;

        $this->localeArray = $this->combine(
            $this->localeArray,
            $this->ConfigLoader->load_locale(Registry::instance()->User()->getDefaultLocale())
        );

        $this->ConfigLoader = null;
    }

    private function combine(array $to, array $from)
    {
        $result = array();

        foreach($from as $key => $value)
        {
            if(is_array($value))
            {
                if(!isset($to[$key])) $to[$key] = array();
                $result[$key] = $this->combine($to[$key], $value);
            }
            else
            {
                if(!isset($to[$key]))
                    $result[$key] = $value;
                else
                    $result[$key] = $to[$key];
            }
        }

        $d = null;
        $e = null;

        return $result;
    }

    public function offsetExists($offset)
    {
        $exists = isset($this->localeArray[$offset]);

        if($exists) return true;

        if($this->defaultLocaleLoaded) return $exists;

        $this->addAnotherLocale();

        return $this->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        $var = trim($offset);
        if(empty($var)) return null;

        if($this->offsetExists($offset))
            return $this->localeArray[$offset];
        else
            return null;
    }

    public function offsetSet($offset, $value)
    {
        throw new UnsupportedOperation();
    }

    public function offsetUnset($offset)
    {
        throw new UnsupportedOperation();
    }
}