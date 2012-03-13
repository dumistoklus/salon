<?php

namespace Orgup\Modules\Index\Administration;
use Orgup\Application\Registry;
use Orgup\Plugins\Managers\CustomPageManager;

class PageEdit extends AdministrationModuleBuilder {

    public function run() {

        if ( $this->Routing->route_get('mess') == 'page_created' ) {
            $this->Data->add_notification('page_created', 'admin' );
            $this->Ways->delete_param('mess');
        }

        $PageManager = new CustomPageManager( Registry::instance()->get('db'), $this->Routing );
        $id = $this->getWayPattern(0);
        $PageManager->set_page_id( $id );

        if ( $PageManager->check_changes() ) {

            if ( $PageManager->update() ) {

                $this->Data->add_notification('saved', 'admin' );
            } else {
                $this->Data->add_error( $PageManager->get_errors(), 'admin');
            }
        }

        $this->Data->setPage( $PageManager->get_PageModel() );
        $this->Data->setPageId( $id );
    }
}