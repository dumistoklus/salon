<?php

namespace Orgup\Modules\Index;
use \Orgup\Modules\IndexModuleBuilder;

class Error404 extends IndexModuleBuilder {

    public function run() {
        @header("HTTP/1.1 404 Not found");
    }
}