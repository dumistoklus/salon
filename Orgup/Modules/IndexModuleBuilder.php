<?php

namespace Orgup\Modules;
use Orgup\Application\Authentication;
use Orgup\Application\Redirect;
use Orgup\Application\Registry;


class IndexModuleBuilder extends ModuleBuilder {

    /**
     * @var \Orgup\DataModels\IndexData
     */
    protected $Data;

    public function actionsBeforeRun()
    {
        if ( $this->Routing->route_get('logout') ) {
            $Authentication = new Authentication( Registry::instance()->get('db'), $this->Routing );
            $Authentication->logout();

            throw new Redirect(  $this->Ways->delete_param('logout') );
        }
    }

    public function actionsAfterRun() {
        $this->Data->initStylesAndScripts();
    }

}