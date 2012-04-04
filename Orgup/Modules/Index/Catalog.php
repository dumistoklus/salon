<?php

namespace Orgup\Modules\Index;
use \Orgup\Modules\IndexModuleBuilder;
use \Orgup\Application\Registry;

class Catalog extends IndexModuleBuilder {

    /**
     * @var \Orgup\DataModels\Index\Catalog
     */
    protected $Data;

    public function run() {

        $this->Data->setMainCat( $this->getWayPattern(0) );
        $this->Data->setGoods( $this->getGoods( $this->getWayPattern(0) ) );
        $this->Data->setCats( $this->getCats() );
    }

    public function getCats() {
        return $this->getDB()->fetchAll('
            SELECT cat.*, img.image_id, img.ext FROM catalogs AS cat
             LEFT JOIN `goods` AS goods USING(cat_id)
             LEFT JOIN `images` AS img USING(id)
             GROUP BY cat.cat_id
            ORDER BY cat.`parent`
        ');
    }

    public function getGoods( $cat_id ) {
        return $this->getDB()->fetchAll('SELECT goods.*, img.image_id, img.ext FROM `goods` as goods LEFT JOIN `images` AS img USING(id) WHERE goods.`cat_id` = ? GROUP BY goods.id ORDER BY goods.`name`', array( $cat_id ) );
    }
}