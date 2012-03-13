<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 31.08.11
 * Time: 15:13
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Application;

class Rights {

    private static $instance;

    private $rights;

    public static function instatnce(array $rights = null)
    {
       if(self::$instance == null)
           return self::$instance = new self($rights);
       return self::$instance;
    }

    private function __construct(array $rights)
    {
        $this->rights = $rights;
    }

    public function getRights()
    {
        return $this->rights;
    }

    public function issetRight($name)
    {
        return in_array($this->rights, $name);
    }
}
