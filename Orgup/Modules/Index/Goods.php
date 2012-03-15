<?php

namespace Orgup\Modules\Index;
use \Orgup\Modules\IndexModuleBuilder;
use Orgup\Application\Exception\Module\E404;

class Goods extends IndexModuleBuilder {

    /**
     * @var \Orgup\DataModels\Index\Goods
     */
    protected $Data;

    public function run() {
        $goods = $this->getGoods();

        if ( $goods ) {
            $this->Data->setGoods( $goods );
            $this->Data->setCats( $this->getCats() );
        } else {
            throw new E404;
        }
    }

    private function getGoods() {
        return $this->getDB()->fetchAssoc('SELECT goods.*, img.ext, img.image_id as image, fab.name as fabrika_name, fab.`fabrika_id`, ct.name as country FROM goods as goods
        LEFT JOIN `fabriki` AS fab ON goods.fabrika_id = fab.fabrika_id
        LEFT JOIN `images` AS img ON goods.id = img.id 
        LEFT JOIN `country` AS ct ON goods.country_id = ct.country_id
        WHERE goods.id = ?', array( $this->getWayPattern(0)) );
    }

    public function getCats() {
        return $this->getDB()->fetchAll('SELECT * FROM catalogs ORDER BY `parent`');
    }
}