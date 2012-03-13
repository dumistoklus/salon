<?php

namespace Orgup\DataModels\Index\Administration\Catalog;
use \Orgup\DataModels\Index\Administration\Administration;

class Goods extends Administration {

    private $_goods;

    public function initStylesAndScripts() {
        $this->add_style('catalog_list');
    }

    public function setGoods( $goods ) {
        $this->_goods = $goods;
    }

    public function goods() {
        return $this->_goods;
    }
}