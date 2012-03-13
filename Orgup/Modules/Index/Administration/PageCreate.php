<?php

namespace Orgup\Modules\Index\Administration;
use Orgup\Application\Registry;
use Orgup\Application\Redirect;
use Orgup\Plugins\Managers\CustomPageManager;

class PageCreate extends AdministrationModuleBuilder {

    public function run() {

        $PageManager = new CustomPageManager( Registry::instance()->get('db'), $this->Routing );

        if ( $PageManager->ready_to_create() ) {

            if ( $PageManager->create() ) {

                $new_id = $PageManager->get_new_id();
                throw new Redirect( $this->Ways->add( 'mess', 'page_created', $this->Ways->admin_custom_page( $new_id ) ) );

            } else {
                $this->Data->add_error( $PageManager->get_errors(), 'admin');
            }
        }

        $this->Data->setPage( $PageManager->get_PageModel() );

        // отмечаем, что это создание страницы
        $this->Data->thisIsCreating();
    }
}