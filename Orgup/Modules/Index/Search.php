<?php

namespace Orgup\Modules\Index;
use \Orgup\Modules\IndexModuleBuilder;
use \Orgup\Application\Registry;

class Search extends IndexModuleBuilder {

    /**
     * @var \Orgup\DataModels\Index\Search
     */
    protected $Data;

    public function run() {
        $this->Data->setResults( $this->getDB()->fetchAll('SELECT * FROM goods LIMIT 10') );
    }
}