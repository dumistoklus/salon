<?php

namespace Orgup\DataModels\Index;
use \Orgup\DataModels\IndexData;

class Mainpage extends IndexData {

    public function initStylesAndScripts() {
        $this->add_script('jcarousel');
        $this->add_script('mainpage');
        $this->run_script('mainpage');
    }

    public function get_description() {
        if ( isset( $this->lang['mainpage']['DESCRIPTION'] ) )
            return $this->lang['mainpage']['DESCRIPTION'];
        return false;
    }

    public function thisIsMainPage() {
        return true;
    }
}