<?php

namespace Orgup\Application;

interface AuthenticationInterface {

    /**
     * @return false|int
     */
    public function get_user_id();

    /**
     * @return bool
     */
    public function logout();

    public function create_session( $id_user, $exp_time = 0, $auth_type = 1 );
}

/*
 * authtypes =
 * 1 - login form
 * 2 - vk
 * 3 - fb
 */
class Authentication implements AuthenticationInterface {

    private $db;
    private $id_user;
    private $Routing;
    private $session_hash;
    private $cookie_checked;

    const VkAuth = 2;
    const FbAuth = 3;
    const TwitterAuth = 4;

    public function __construct( $db, \Orgup\Application\Routing $Routing ) {
        $this->db = $db;
        $this->Routing = $Routing;
    }

    public function get_user_id() {

        if ( $this->id_user !== null )
            return $this->id_user;

        if ( !$this->get_user_cookie() )
            return false;

        $id_user = $this->db->fetchColumn(
           "SELECT id_user FROM sessions WHERE session_hash = :hash AND hua_md5 = :hua_md5 AND deleted = 0",
            array( ':hash'=> md5( $this->session_hash ), ':hua_md5' => md5( $this->Routing->route_server( 'HTTP_USER_AGENT' ) ) )
        );

        if ( !$id_user )
            $this->delete_user_cookie();

        return $this->id_user = $id_user;
    }

    public function logout() {

        if ( !$this->get_user_cookie() )
            return false;

        $result = $this->db->update(
            'sessions',
            array( 'deleted' => 1 ),
            array('session_hash' => md5( $this->session_hash ), 'hua_md5' => md5( $this->Routing->route_server( 'HTTP_USER_AGENT' ) ) )
        );

        if ( $result ) {
            $this->delete_user_cookie();
            return true;
        }

        return false;
    }

    public function create_session( $id_user, $exp_time = 0, $auth_type = 1 ) {

        if ( $this->get_user_id() )
            return true;

        $session_first_hash = md5( microtime(true) . Registry::instance()->get('SALT') . $id_user );
        $session_second_hash = md5( $session_first_hash );

        $result = $this->db->insert(
            'sessions',
            array(
                'session_hash' => $session_second_hash,
                'id_user'      => $id_user,
                'create_time'  => time(),
                'exp_time'     => $exp_time,
                'hua_md5'      => md5( $this->Routing->route_server( 'HTTP_USER_AGENT' ) ),
                'auth_type'    => $auth_type
            )
        );

        if ( $result ) {
            $this->Routing->setcookie( 'user', $session_first_hash, $exp_time ? time() + $exp_time : null );
            return true;
        }

        return false;
    }

    private function get_user_cookie() {

        if ( is_bool( $this->cookie_checked ) )
            return $this->cookie_checked;

        $session_hash = $this->Routing->route_cookie('user');

        if ( $this->check_session( $session_hash ) ) {
            $this->session_hash = $session_hash;
            return $this->cookie_checked = true;
        }

        return $this->cookie_checked = false;
    }

    private function check_session( $session_hash ) {
        return preg_match( '/^[0-9a-f]{32}$/', $session_hash );
    }

    private function delete_user_cookie() {
        $this->Routing->unset_cookie('user');
    }
}