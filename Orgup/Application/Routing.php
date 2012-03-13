<?php

namespace Orgup\Application;

interface HTTPRouting
{
    public function route_get($key);
    public function route_post($key);
    public function route_cookie($key);
    public function route_files($key);
    public function post();
    public function get();
    public function cookies();
}

class Routing implements HTTPRouting {

    protected $get = array();
    protected $post = array();
    protected $cookie = array();
    protected $server = array();

    public function __construct(){

        $this->get = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
        $this->cookie = $_COOKIE;
        $this->files = $_FILES;

        // for tests
        if ( !isset( $_SERVER['REQUEST_URI'] ) ) {
            $this->server['REQUEST_URI'] = Registry::instance()->get('test');
        }
    }



    public function route_get( $key ) {
        if ( isset( $this->get[$key] ) )
            return $this->get[$key];
        return null;
    }

    public function route_post( $key ) {
        if ( isset( $this->post[$key] ) )
            return $this->post[$key];
        return null;
    }

    public function route_cookie( $key ) {

        if (isset( $this->cookie[$key] ) )
            return $this->cookie[$key];
        return null;
    }

    public function route_server( $key ) {

        if (isset( $this->server[$key] ) )
            return $this->server[$key];
        return null;
    }

    public function route_files( $key ) {

        if (isset( $this->files[$key] ) )
            return $this->files[$key];
        return null;
    }

    public function unset_cookie( $cookie_name ) {
        return setcookie( $cookie_name, null, 0, '/' );
    }

    public function setcookie( $name, $value, $time = 0 ) {
        setcookie( $name, $value, $time, '/' );
    }

    public function post()
    {
        return $this->post;
    }

    public function get()
    {
        return $this->get;
    }

    public function cookies()
    {
        return $this->cookie;
    }

    public function set_get($key, $value)
    {
        $this->get[$key] = $value;
    }

    public function set_post($key, $value)
    {
        $this->post[$key] = $value;
    }

}