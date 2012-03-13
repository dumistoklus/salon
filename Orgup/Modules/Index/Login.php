<?php

namespace Orgup\Modules\Index;

use \Orgup\Application\Registry;
use \Orgup\Application\Redirect;
use \Orgup\Modules\IndexModuleBuilder;

class Login extends IndexModuleBuilder {
    public function run() {

        $User = Registry::instance()->User();

        if ( $User->imember() )
            throw new Redirect('/');

        if ( $this->Routing->route_post('check_login') ) {
            $this->check_sign_in();
        }
    }

    private function check_sign_in() {

        $Authorization = new \Orgup\Common\Authorization(
            $this->Routing->route_post('login'),
            $this->Routing->route_post('password'),
            $this->Routing->route_post('remember_me')
        );

        if ( $Authorization->authorize_me() )
            $this->redirect();

        $this->Data->add_error( 'WRONG_LOGIN_OR_PASSWORD', 'login' );
    }

    private function redirect() {

        $return_to = '/';

        if ( $this->Routing->route_get('return') ) {
            $return_to = $this->Routing->route_get('return');
        }

        $redirect = parse_url($return_to);

        if(isset($redirect['path']))
            $return_to = $redirect['path'];
        
        throw new Redirect( $return_to );
    }
}