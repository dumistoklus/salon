<?php

namespace Orgup\DataModels\Index\Administration\Users;
use \Orgup\DataModels\Index\Administration\Administration;

class UserInfo extends Administration {

    private $user = array();

    public function initStylesAndScripts() {
        $this->add_style('admin_user');
        $this->add_script('user');
        $this->run_script('user');
    }

    public function setUser( array $User ) {
        $this->user = $User;
    }

    public function user() {
        return $this->user;
    }

    public function sendPassword() {
        $send = \Orgup\Application\Registry::instance()->get('Routing')->route_post('send_password');
        $password = \Orgup\Application\Registry::instance()->get('Routing')->route_post('password');
        if ( $send !== null || $password === null ) {
            return true;
        }

        return false;
    }
}