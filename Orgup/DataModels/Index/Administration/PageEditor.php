<?php

namespace Orgup\DataModels\Index\Administration;

class PageEditor extends Administration {

    private $Page;
    private $is_creating = false;
    private $_page_id;

    public function initStylesAndScripts() {
        $this->add_script('ckeditor');
        $this->add_script('ckfinder');
        $this->add_script('limit');
        $this->add_script('pageEditor');
        $this->run_script('pageEditor');
    }

    public function setPage( $Page ) {
        $this->Page = $Page;
    }

    public function getPage() {
        return $this->Page;
    }

    public function thisIsCreating() {
        $this->is_creating = true;
    }

    /**
     * @return bool
     */
    public function getIsCreating() {
        return $this->is_creating;
    }

    public function setPageId( $page_id ) {
        $this->_page_id = $page_id;
    }

    public function pageId() {
        return $this->_page_id;
    }
}