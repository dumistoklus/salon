<?php

namespace Orgup\Modules\Index\Administration;
use \Orgup\Modules\IndexModuleBuilder;

class AdministrationModuleBuilder extends IndexModuleBuilder {

    public function actionsBeforeRun() {

        parent::actionsBeforeRun();

        $this->Data->setNavigation( $this->Ways->get_admin_menu_massive() );
    }

    protected function get_current_page() {

        $page = 1;
        $page_from_route = $this->Routing->route_get('p');
        if ( (int)$page_from_route > 0 )
            $page = (int)$page_from_route;

        return $page;
    }
}
