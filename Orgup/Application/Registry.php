<?php
namespace Orgup\Application;

class Registry {

    private $_cache_stack = array();

    // public function set($key, &$item)  почему передается по ссылке?  http://wiki.agiledev.ru/doku.php?id=ooad:manage_dependencies_in_php_code
    public function set($key, $item) {
        $this->_cache_stack[$key] = &$item;
    }

    public function &get($key) {
        return $this->_cache_stack[$key];
    }

    public static function &instance() {
        static $registry = false;
        if (!$registry) {
            $registry = new Registry();
        }
        return $registry;
    }

    /**
     * @return \Orgup\Common\User
     */
    public function User() {
        return $this->_cache_stack['User'];
    }
}