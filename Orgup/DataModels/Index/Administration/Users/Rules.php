<?php

namespace Orgup\DataModels\Index\Administration\Users;
use \Orgup\DataModels\Index\Administration\Administration;

class Rules extends Administration {

    private $rules_list = array();
    private $_user_id;
    private $_userName;

    public function initStylesAndScripts() {
         $this->add_style('rules');
    }

    public function getRulesList() {
        return $this->rules_list;
    }

    public function userId() {
        return $this->_user_id;
    }

    public function setUserId( $user_id ) {
        $this->_user_id = $user_id;
    }

    public function set_rules_list( $rules_list ) {
        $this->rules_list = $rules_list;
    }

    public function setUserName( $userName ) {
        $this->_userName = $userName;
    }

    public function userName() {
        return $this->_userName;
    }
}