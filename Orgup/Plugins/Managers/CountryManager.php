<?php

namespace Orgup\Plugins\Managers;

class CountryManager {

    private $_country = array();
    private $_data_obtained = false;

    public function getCountry() {

        if ( $this->_data_obtained ) {
            return $this->_country;
        }

        return $this->get_cats_from_bd();
    }

    public function get_cats_from_bd() {

        $db = \Orgup\Application\Registry::instance()->get('db');
        $result = $db->fetchAll('SELECT name, country_id FROM `country`');

        $country = array();

        if ( !empty( $result ) ) {
            foreach( $result as $c ) {
                $country[$c['country_id']] = $c['name'];
            }
        }

        $this->_data_obtained = true;
        return $this->_country = $country;
    }
}