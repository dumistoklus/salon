<?php

namespace Orgup\Modules\Index\Administration\Catalog;
use \Orgup\Modules\Index\Administration\AdministrationModuleBuilder;

class CatalogList extends AdministrationModuleBuilder {

    /**
     * @var \Orgup\DataModels\Index\Administration\Catalog\CatalogList
     */
    protected $Data;

    public function run() {

        // удаляем каталог
        if ( $this->Routing->route_get('remove') ) {

            $id_cat = (int)$this->Routing->route_get('remove');
            if ( $id_cat > 0 ) {
                $this->remove_catalog( $id_cat );
            }

            $this->Ways->delete_param('remove');
        }

        // редактируем
        if ( $this->Routing->route_get('edit') ) {

            $id_cat = (int)$this->Routing->route_get('edit');

            if ( $id_cat > 0 ) {
                $this->Data->thisIsEditing();

                if( $this->Routing->route_post('name') !== null ) {
                    $this->edit_catalog( $id_cat );
                } else {
                    $this->Data->setQuery( $this->get_catalog_id( $id_cat ) );
                }
            }

        } // создаем

        else if ( $this->Routing->route_post('name') !== null ) {

             $this->create_catalog();
         }

        if ( $this->Routing->route_get('added') ) {
            $this->Ways->delete_param('added');
            $this->Data->cat_edited();
        }

        if ( $this->Routing->route_get('created') ) {
            $this->Ways->delete_param('created');
            $this->Data->cat_created();
        }

        $this->Data->setCatalog( $this->get_catalog() );
    }

    private function get_catalog() {
        return $this->getDB()->fetchAll('SELECT * FROM `catalogs` ORDER BY `name`');
    }

    private function remove_catalog( $id ) {
        if ( $this->getDB()->delete( 'catalogs', array('cat_id' => $id ) ) ) {
            $this->Data->catalogDeleted();
        } else {
            $this->Data->catalogNotDeleted();
        }
    }

    private function edit_catalog( $id ) {

        $query = $this->Data->query();

        if ( $this->check_query( $query ) ) {
            if ( $this->getDB()->update( 'catalogs', $query, array('cat_id' => $id ) ) ) {
                throw new \Orgup\Application\Redirect( $this->Ways->add( 'added', 1, $this->Ways->thispage() ) );
            }
        }
    }

    private function check_query( $query ) {

        if ( empty( $query['name'] ) ) {
            $this->Data->add_error('Имя не может быть пустым!', 'catalog');
            return false;
        }

        if ( !$this->get_catalog_id( $query['parent'] ) && $query['parent'] > 0 ) {
            $this->Data->add_error('такого подкаталога не существует!', 'catalog');
            return false;
        }

        return true;
    }

    private function get_catalog_id( $id ) {
        return $this->getDB()->fetchAssoc('SELECT * FROM `catalogs` WHERE `cat_id` = '.$id );
    }

    private function create_catalog() {

        $query = $this->Data->query();

        if ( $this->check_query( $query ) ) {
            if ( $this->getDB()->insert( 'catalogs',  $query ) ) {
                throw new \Orgup\Application\Redirect( $this->Ways->add( 'created', 1, $this->Ways->thispage() ) );
            }
        }
    }
}
