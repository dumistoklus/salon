<?php

namespace Orgup\Modules\Index\Administration;

class Country extends AdministrationModuleBuilder {

    /**
     * @var \Orgup\DataModels\Index\Administration\Country
     */
    protected $Data;

    public function run() {

        $delete = (int)$this->Routing->route_get('delete');
        if ( $delete ) {
            if ( $this->getDB()->delete('country', array( 'country_id' => $delete ) ) ) {
                $this->getDB()->update('goods', array('country_id' => 1 ), array( 'country_id' => $delete ) ) ;
                throw new \Orgup\Application\Redirect($this->Ways->add( 'deleted', 1, $this->Ways->admin_country() ) );
            }
        }

        if ( $this->Routing->route_get('deleted') ) {
            $this->Data->add_notification('Удалено','2');
        }

        $country = $this->getCountry();

        $id = (int)$this->Routing->route_get('id');
        $name = $this->Routing->route_post('name');

        if ( $id && $name ) {
            if ( $this->getDB()->update('country', array('name' => (string)$name ), array( 'country_id' => $id ) ) ) {
                $this->Data->add_notification('Сохранено', 'sdds');
                $country = $this->getCountry();
            }
        } else if ( $name ) {
            if ( $this->getDB()->insert('country', array('name' => (string)$name ) ) ) {
                $this->Data->add_notification('Создано', 'sdds');
                $country = $this->getCountry();
            }
        }

        if ( $id ) {

            foreach ( $country as $f ) {
                if ( $f['country_id'] == $id ) {
                    if ( !$name ) {
                        $name = $f['name'];
                    }
                    $this->Data->setName( $name );
                    $this->Data->setFabrikaId( $id );
                    break;
                }
            }
        }

        $this->Data->setCountry( $country );
    }

    private function getCountry() {
        return $this->getDB()->fetchAll('SELECT * FROM country');
    }
}