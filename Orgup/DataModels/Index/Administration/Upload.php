<?php

namespace Orgup\DataModels\Index\Administration;

class Upload extends Administration {

    private $name;
    private $error = array();

    public function getName() {
        return $this->name;
    }

    public function setName( $name ) {
        $this->name = $name;
    }
}