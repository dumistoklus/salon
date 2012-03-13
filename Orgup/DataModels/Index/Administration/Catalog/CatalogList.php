<?php

namespace Orgup\DataModels\Index\Administration\Catalog;
use \Orgup\DataModels\Index\Administration\Administration;

class CatalogList extends Administration {

    public function initStylesAndScripts() {
        $this->add_style('catalog_list');
    }

    private $catalog = array();
    private $catalog_key_value = array( 0 => '- - -' );
    private $is_editing = false;
    private $_query;

    public function setCatalog( $catalog ) {

        $this->catalog = $this->catalog_sort2( $catalog );
        foreach ( $catalog as $cat ) {
            $this->catalog_key_value[$cat['cat_id']] = $cat['name'];
        }
    }

    private function catalog_sort2( $from_bd ) {

        $catalog = array();

        foreach ( $from_bd as $cat ) {
            $catalog[$cat['parent']][] = $cat;
        }

        return $catalog;
    }

    public function catalog() {
        return $this->catalog;
    }

    public function catalogDeleted() {
        $this->add_notification( 'Каталог удален!', 'catalog' );
    }

    public function catalogNotDeleted() {
        $this->add_error( 'Каталог не удален!', 'catalog' );
    }

    public function cat_edited() {
        $this->add_notification( 'Каталог отредактирован!', 'catalog' );
    }

    public function cat_created() {
        $this->add_notification( 'Каталог создан!', 'catalog' );
    }

    public function catsAsKeyValue() {
        return $this->catalog_key_value;
    }

    public function isEditing() {
        return $this->is_editing;
    }

    public function thisIsEditing() {
        $this->is_editing = true;
    }

    public function setQuery( $query ) {
        $this->_query = $query;
    }

    public function query() {

        if ( !$this->_query ) {
            $Routing = \Orgup\Application\Registry::instance()->get('Routing');
            $this->_query['name'] = trim( (string)$Routing->route_post('name') );
            $this->_query['parent'] = (int)$Routing->route_post('parent');
        }

        return $this->_query;
    }
}