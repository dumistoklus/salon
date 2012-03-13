<?php

namespace Orgup\DataModels\Index;
use \Orgup\DataModels\IndexData;

class Goods extends IndexData {

    private $goods;
    private $catalog;
    private $catalog_key_value;
    private $hleb;

    public function initStylesAndScripts() {
        $this->add_style('goods');
        $this->add_style('catalog');
        $this->add_script('lightbox');
        $this->add_script('goods');
        $this->add_style('lightbox');
        $this->run_script('goods');
    }

    public function setCats( $catalog ) {

        $this->catalog = $this->catalog_sort( $catalog );

        foreach ( $catalog as $cat ) {
            $this->catalog_key_value[$cat['cat_id']] = $cat;
        }

        $this->hleb = $this->get_parent( array(), $this->goods['cat_id'] );
    }

    private function get_parent( $array, $key ) {

        if ( $key == 0 OR !isset( $this->catalog_key_value[$key] ) )
            return $array;

        array_unshift( $array, array( 'cat_id' => $key, 'name' => $this->catalog_key_value[$key]['name'] ) );

        return $this->get_parent( $array, $this->catalog_key_value[$key]['parent'] );
    }

    public function setGoods($goods)
    {
        $this->goods = $goods;
    }

    public function getGoods()
    {
        return $this->goods;
    }

    public function image() {
        return 'images/b/'.$this->goods['image'].'.'.$this->goods['ext'];
    }

    public function big_image() {
        return 'images/o/'.$this->goods['image'].'.'.$this->goods['ext'];
    }

    public function getHleb()
    {
        return $this->hleb;
    }
}