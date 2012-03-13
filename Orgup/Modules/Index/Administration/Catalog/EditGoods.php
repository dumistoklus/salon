<?php

namespace Orgup\Modules\Index\Administration\Catalog;
use \Orgup\Modules\Index\Administration\AdministrationModuleBuilder;

class EditGoods extends AdministrationModuleBuilder {

    /**
     * @var \Orgup\DataModels\Index\Administration\Catalog\GoodsEditor
     */
    protected $Data;

    public function run() {

        $this->Data->initGoods();
        $this->Data->thisIsEditing();

        $goods = $this->getGoods( $this->getWayPattern(0) );

        if ( $this->Data->dataIsObtained() ) {

            if ( $this->Data->isDataNotHaveErrors() && $goods ) {
                if ( $id = $this->update_goods( $this->Data->getObtainedGoods(), $this->getWayPattern(0) ) ) {
                    $this->Data->add_notification( 'Данные обновлены!', 'goods');
                }
            }

        } else {

            $this->Data->setGoods( $goods );
        }

        if ( $this->Routing->route_get('created') ) {
            $this->Ways->delete_param('created');
            $this->Data->add_notification('Товар создан!', 'goods');
        }

        if ( $this->Routing->route_get('remove_image') ) {
            $this->Ways->delete_param('remove_image');
        }

        if ( $goods ) {
            $this->Data->setId( $this->getWayPattern( 0 ) ) ;
            $this->Data->uploadPhoto( $this->getWayPattern(0) );
            $this->Data->delete_photo();
            $this->Data->setImages( $this->getImages() );
        }
    }

    public function update_goods( $Goods, $id ) {

        return $this->getDB()->update( 'goods', $Goods->asArray(), array( 'id' => $id ) );
    }

    public function getGoods( $id ) {

        $result = $this->getDB()->fetchAssoc('SELECT * FROM `goods` WHERE `id` = ?', array( $id ) );

        if ( $result ) {
            return $result;
        } else {
            $this->Data->add_error( 'Такого товара не существует', 'goods' );
            return false;
        }
    }

    public function getImages() {
        return $this->getDB()->fetchAll('SELECT * FROM `images` WHERE `id` = ? ORDER BY `image_id`', array( $this->getWayPattern(0) ) );
    }
}