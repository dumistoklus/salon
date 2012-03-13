<?php

namespace Orgup\Modules\Index\Administration\Catalog;
use \Orgup\Modules\Index\Administration\AdministrationModuleBuilder;

class NewGoods extends AdministrationModuleBuilder {

    /**
     * @var \Orgup\DataModels\Index\Administration\Catalog\GoodsEditor
     */
    protected $Data;

    public function run() {

        $this->Data->initGoods();

        if ( $this->Data->dataIsObtained() && $this->Data->isDataNotHaveErrors() ) {
            if ( $id = $this->create_goods( $this->Data->getObtainedGoods() ) ) {
                $this->Data->uploadPhoto( $id );
                throw new \Orgup\Application\Redirect( $this->Ways->add( 'created', 1, $this->Ways->admin_goods_edit( $id ) ) );
            }
        }
    }

    public function create_goods( $Goods ) {

        if ( $this->getDB()->insert( 'goods', $Goods->asArray() ) ) {
            return $this->getDB()->lastInsertId();
        }

        return false;
    }
}