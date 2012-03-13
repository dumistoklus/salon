<?php

namespace Orgup\Modules\Index\Administration\Catalog;
use \Orgup\Modules\Index\Administration\AdministrationModuleBuilder;

class Goods extends AdministrationModuleBuilder {

    const LIMIT = 30;

    /**
     * @var \Orgup\DataModels\Index\Administration\Catalog\Goods
     */
    protected $Data;

    public function run() {

        if ( $this->Routing->route_get('goods_removed') ) {
            $this->Ways->delete_param('goods_removed');
            $this->Data->add_notification('Товар удален', 'goods');
        }

        $p = (int)$this->Routing->route_get('p');
        if ( $p <= 0 ) {
            $p = 1;
        }

        if ( $this->Routing->route_get('remove_goods') ) {

            $id = (int)$this->Routing->route_get('remove_goods');
            if ( $id > 0 )
                if ( $this->remove_goods( $id ) ) {
                    $count = $this->getDB()->fetchColumn('SELECT COUNT(*) FROM `goods`');
                    if ( ceil( $count / self::LIMIT ) < $p ) {
                        $p = ( $p > 1 ) ? $p - 1 : 1;
                    }

                    $way = ( $p > 1 ) ? $this->Ways->add( 'p', $p ) : $this->Ways->admin_goods();

                    throw new \Orgup\Application\Redirect( $this->Ways->add( 'goods_removed', 1, $way ) );
                }
        }

        $result = $this->getDB()->fetchAll('SELECT g.*, cat.`name` as `cat_name`, fab.`name` as `fabrika_name`, fab.`fabrika_id` FROM `goods` as g
        LEFT JOIN `fabriki` as fab ON g.`fabrika_id` = fab.`fabrika_id`
        LEFT JOIN `catalogs` as cat ON g.`cat_id` = cat.`cat_id` ORDER BY id DESC LIMIT '.self::LIMIT * ($p - 1).','.self::LIMIT );
        $this->Data->setGoods( $result );

        if ( $result ) {
            $count = $this->getDB()->fetchColumn('SELECT COUNT(*) FROM `goods`');
            $this->Data->add_paginator( $count, $p, self::LIMIT );
        }
    }

    public function remove_goods( $id ) {
        if ( $this->getDB()->delete('goods', array( 'id' => $id ) ) ) {
            $this->getDB()->delete('images', array( 'id' => $id ) );
            return true;
        }

        return false;
    }
}