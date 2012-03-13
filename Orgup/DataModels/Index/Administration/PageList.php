<?php

namespace Orgup\DataModels\Index\Administration;

class PageList extends Administration {

    private $pages = array();

    public function set_pagesList( $pagesList ) {
        $this->pages = $pagesList;
    }

    public function get_pages() {
        return $this->pages;
    }
}