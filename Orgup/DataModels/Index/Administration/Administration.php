<?php

namespace Orgup\DataModels\Index\Administration;
use \Orgup\DataModels\IndexData;

abstract class Administration extends IndexData {

    protected $navigation;

    public function getNavigation() {
        return $this->navigation;
    }

    public function setNavigation( $navigation ) {
        $this->navigation = $navigation;
    }

    /*
     * переопределяем стили, установленные в IndexData
     */
    protected function init_styles(){
        $this->add_style('administration');
    }
}