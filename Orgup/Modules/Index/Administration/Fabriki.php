<?php

namespace Orgup\Modules\Index\Administration;

class Fabriki extends AdministrationModuleBuilder {

    /**
     * @var \Orgup\DataModels\Index\Administration\Fabriki
     */
    protected $Data;

    public function run() {

        $delete = (int)$this->Routing->route_get('delete');
        if ( $delete ) {
            if ( $this->getDB()->delete('fabriki', array( 'fabrika_id' => $delete ) ) ) {
                $this->getDB()->update('goods', array('fabrika_id' => 1 ), array( 'fabrika_id' => $delete ) ) ;
                throw new \Orgup\Application\Redirect($this->Ways->add( 'deleted', 1, $this->Ways->admin_fabriki() ) );
            }
        }

        if ( $this->Routing->route_get('deleted') ) {
            $this->Data->add_notification('Удалено','2');
        }

        $fabriki = $this->getFabriki();

        $id = (int)$this->Routing->route_get('id');
        $name = $this->Routing->route_post('name');

        if ( $id && $name ) {
            if ( $this->getDB()->update('fabriki', array('name' => (string)$name ), array( 'fabrika_id' => $id ) ) ) {
                $this->Data->add_notification('Сохранено', 'sdds');
                $fabriki = $this->getFabriki();
            }
        } else if ( $name ) {
            if ( $this->getDB()->insert('fabriki', array('name' => (string)$name ) ) ) {
                $this->Data->add_notification('Создано', 'sdds');
                $fabriki = $this->getFabriki();
            }
        }

        if ( $id ) {

            foreach ( $fabriki as $f ) {
                if ( $f['fabrika_id'] == $id ) {
                    if ( !$name ) {
                        $name = $f['name'];
                    }
                    $this->Data->setName( $name );
                    $this->Data->setFabrikaId( $id );
                    break;
                }
            }
        }

        $this->Data->setFabriki( $fabriki );
    }

    private function getFabriki() {
        return $this->getDB()->fetchAll('SELECT * FROM fabriki');
    }
}