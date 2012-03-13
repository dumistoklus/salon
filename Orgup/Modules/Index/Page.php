<?php

namespace Orgup\Modules\Index;
use \Orgup\Modules\IndexModuleBuilder;
use \Orgup\Application\Registry;

class Page extends IndexModuleBuilder {
    public function run() {

        $Page = new PageOne( $this->getWayPattern(0) );
        $pageById =  $Page->page();
        if($pageById == false)
             throw new \Orgup\Application\Exception\Module\E404;
        $this->Data->setPage($pageById);
        $this->Data->add_title($pageById['title']);
        $this->Data->set_keywords($pageById['keywords']);
        $this->Data->set_description($pageById['description']);
    }
}

class PageOne {

    private $page = array();
    private $id;

    public function __construct($id) {
        $this->id = (int)$id;
    }

    public function page() {
        if (count($this->page) == 0) {
            $this->initPage();
        }
        return $this->page;
    }

    private function initPage() {
        $this->page = Registry::instance()->get('db')->fetchAssoc('SELECT * FROM `custom_pages` WHERE id_page = ?', array($this->id));
    }
}
