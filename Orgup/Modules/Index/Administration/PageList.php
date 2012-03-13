<?php

namespace Orgup\Modules\Index\Administration;
use Orgup\Application\Registry;
use Orgup\Application\Redirect;
use Orgup\Plugins\Managers\CustomPageManager;

class PageList extends AdministrationModuleBuilder {

    const PAGES_PER_PAGE = 10;

    public function run() {

        $this->delete_page();

        $current_page = $this->get_current_page();

        $pages = $this->get_pages_from_bd( $current_page );

        if ( !empty ( $pages ) ) {
            $this->Data->set_pageslist( $pages );

            $total = $this->get_page_count();
            $this->Data->add_paginator( $total, $current_page, self::PAGES_PER_PAGE );
        }
    }

    private function get_pages_from_bd( $page ) {

        $queryResult = \Orgup\Application\Registry::instance()->get('db')->fetchAll(
            "SELECT SQL_CALC_FOUND_ROWS * FROM `custom_pages` LIMIT ".( ( $page - 1 ) * self::PAGES_PER_PAGE ) .",".self::PAGES_PER_PAGE
        );

        if ( $queryResult ) {
            return $queryResult;
        } else {
            return array();
        }
    }

    private function get_page_count() {
        $total = \Orgup\Application\Registry::instance()->get('db')->fetchColumn("SELECT FOUND_ROWS()");
        return (int)$total;
    }

    private function delete_page() {

        if ( $this->Routing->route_get('mess') == 'page_deleted') {
            $this->Data->add_notification('page_deleted', 'admin');
        }

        if ( $this->Routing->route_get('delete_page') ) {

            $PageManager = new CustomPageManager( Registry::instance()->get('db'), $this->Routing );
            $PageManager->set_page_id( $this->Routing->route_get('delete_page') );

            if ( $PageManager->delete() ) {
                throw new Redirect( $this->Ways->add( 'mess', 'page_deleted', $this->Ways->delete_param( 'delete_page' ) ) );
            } else {
                $this->Data->add_error( $PageManager->get_errors(), 'admin');
            }
        }
    }
}