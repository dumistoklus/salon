<?php

namespace Orgup\DataModels\Index;
use \Orgup\DataModels\IndexData;

class Page extends IndexData {

    private $page = array();

    public function getPage() {
        return $this->page;
    }

    public function setPage( $page ) {
        $this->page = $page;
    }
}