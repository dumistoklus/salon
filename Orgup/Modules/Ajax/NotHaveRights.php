<?php

namespace Orgup\Modules\Ajax;
use Orgup\Modules\AjaxModuleBuilder;

class NotHaveRights extends AjaxModuleBuilder {

    public function run() {
        $this->Data->add_error( 'YOU_NOT_HAVE_RIGHTS', 'ajax' );
    }
}