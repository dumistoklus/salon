<?php

namespace Orgup\DataModels\Ajax;
use Orgup\DataModels\AjaxData;

class Error404 extends AjaxData {

    public function __construct() {
        parent::__construct();
        $this->add_error('WRONG_RESPONSE', 'ajax');
    }
}
