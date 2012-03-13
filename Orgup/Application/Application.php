<?php

namespace Orgup\Application;

use Doctrine\DBAL\Connection;
use Orgup\Application\Logger;

class Application {
    /**
     * @var \Closure
     */
    private $function_after;
    /**
     * @var \Closure
     */
    private $function_before;
    /**
     * @var \Closure
     */
    private $main_functions;

    public function run(){

        $this->run_before();

        try {

            if ( !is_null( $this->main_functions ) ) {
                $output = $this->main_functions;
                $output();
            }

        } catch ( Redirect $e ) {

            $this->end();
            $this->do_redirect( $e->redirect_to() );
        }

        $this->end();
    }

    public function app( $function ) {
        $this->main_functions = $function;
    }

    public function after( $function ) {
        $this->function_after = $function;
    }

    public function before( $function ) {
        $this->function_before = $function;
    }

    private function end() {
        if ( !is_null( $this->function_after ) ) {
            $function_after = $this->function_after;
            $function_after();
        }

        Logger::log( 'End of Application', __FILE__, __LINE__ );
    }

    private function run_before(){
        if ( !is_null( $this->function_before ) ) {
            $function_before = $this->function_before;
            $function_before();
        }
    }

    private function do_redirect( $redirect_to ) {
        header( "Location: ".$redirect_to );
        die();
    }

    function __destruct()
    {
        Registry::instance()->get('db')->close();
    }
}