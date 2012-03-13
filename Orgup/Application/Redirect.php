<?php

namespace Orgup\Application;
use \Orgup\Application\Exception\Module\E404;

class Redirect extends \Exception {

    private $redirect_to;

    public function __construct( $to = null ) {
        if ( $to === null ) {
            $to = Registry::instance()->get('Path')->get_full_path();
        }
        
        $this->redirect_to = $to;
    }

    public function redirect_to() {
        return $this->redirect_to;
    }
}