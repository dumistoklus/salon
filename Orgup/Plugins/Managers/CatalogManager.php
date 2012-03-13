<?php

namespace Orgup\Plugins\Managers;

class CatalogManager {

    private $_cats = array();
    private $_data_obtained = false;

    public function getCats() {

        if ( $this->_data_obtained ) {
            return $this->_cats;
        }

        return $this->get_cats_from_bd();
    }

    public function get_cats_from_bd() {

        $db = \Orgup\Application\Registry::instance()->get('db');
        $result = $db->fetchAll('SELECT name, cat_id FROM `catalogs`');

        $cats = array();

        if ( !empty( $result ) ) {
            foreach( $result as $cat ) {
                $cats[$cat['cat_id']] = $cat['name'];
            }
        }

        $this->_data_obtained = true;
        return $this->_cats = $cats;
    }
}