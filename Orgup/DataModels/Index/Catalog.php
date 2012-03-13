<?php

namespace Orgup\DataModels\Index;
use \Orgup\DataModels\IndexData;

class Catalog extends IndexData {

    private $mainCat;
    private $catalog;
    private $catalog_key_value;
    private $hleb;
    private $goods;

    public  function initStylesAndScripts() {
        $this->add_style('catalog');
    }

    public function setCats( $catalog ) {

        $this->catalog = $this->catalog_sort( $catalog );

        foreach ( $catalog as $cat ) {
            $this->catalog_key_value[$cat['cat_id']] = $cat;
        }

        $this->hleb = $this->get_parent( array(), $this->mainCat );
    }

    private function get_parent( $array, $key ) {

        if ( $key == 0 OR !isset( $this->catalog_key_value[$key] ) )
            return $array;

        array_unshift( $array, array( 'cat_id' => $key, 'name' => $this->catalog_key_value[$key]['name'], 'image_id' => $this->catalog_key_value[$key]['image_id'], 'ext' => $this->catalog_key_value[$key]['ext'] ) );

        return $this->get_parent( $array, $this->catalog_key_value[$key]['parent'] );
    }

    public function catKeyValue() {
        return $this->catalog_key_value;
    }

    public function getMainCat() {
        return $this->mainCat;
    }

    public function setMainCat( $mainCat )
    {
        $this->mainCat = $mainCat;
    }

    public function getCatalog()
    {
        return $this->catalog;
    }

    public function getHleb()
    {
        return $this->hleb;
    }

    public function image_path( $file, $ext ) {
        return '/images/m/'.$file.'.'.$ext;
    }

    public function setGoods( $goods ) {
        $this->goods = $goods;
    }

    public function getGoods()
    {
        return $this->goods;
    }
}