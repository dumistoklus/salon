<?php

namespace Orgup\Common;

use Orgup\Application\Registry;

class Authorization {

    const EXP_COOKIE_TIME = 1290600; // two weeks

    private $login;
    private $password;
    private $user;
    private $exp_time = 0;

    public function __construct( $login, $password, $remember_me ) {

        $this->login = $login;
        $this->password = $password;

        if ( $remember_me ) {
            $this->exp_time = self::EXP_COOKIE_TIME;
        }

        $this->Authentication = new \Orgup\Application\Authentication( Registry::instance()->get('db'), Registry::instance()->get('Routing') );
    }

    public function authorize_me() {

        if ( !$this->check_login() )
            return false;

        if ( !$this->check_password() )
            return false;

        if ( !$this->check_user_in_db() )
            return false;

        return $this->Authentication->create_session( $this->user['id_user'], $this->exp_time );
    }

    private function check_login() {
        return UserAuthDataValidator::valid_login($this->login);
    }

    private function check_password() {
        return UserAuthDataValidator::valid_password($this->password);
    }

    private function check_user_in_db() {
        $this->user = Registry::instance()->get('db')->fetchAssoc('SELECT * FROM users WHERE `username` = :username AND `password` = :password AND `active` = "1"',
            array(
                ':username' => $this->login,
                ':password' => Hash::password( $this->password )
            )
        );

        return $this->user;
    }
}