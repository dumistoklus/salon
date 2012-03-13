<?php

namespace Orgup\Plugins\Managers;

class FabrikiManager {

    private $_fabriki = array();
    private $_data_obtained = false;

    public function getFabriki() {

        if ( $this->_data_obtained ) {
            return $this->_fabriki;
        }

        return $this->get_cats_from_bd();
    }

    public function get_cats_from_bd() {

        $db = \Orgup\Application\Registry::instance()->get('db');
        $result = $db->fetchAll('SELECT name, fabrika_id FROM `fabriki`');

        $fabriki = array();

        if ( !empty( $result ) ) {
            foreach( $result as $fab ) {
                $fabriki[$fab['fabrika_id']] = $fab['name'];
            }
        }

        $this->_data_obtained = true;
        return $this->_fabriki = $fabriki;
    }
}