<?php

namespace Orgup\Expansions;
use Orgup\Application\Routing;
use Orgup\Application\SpotModule;

class AjaxSpotModule extends SpotModule {

    const ACTION_VALUE = 'ajaxaction';
    const BOX_ACTION = 'boxtype';

    public function __construct( \Orgup\Application\MainLoaderInterface $config, Routing $Routing ) {

        $this->routing_rules = $config->get_routing_rules();
        $this->Routing = $Routing;

        $this->ajax_spot_module( $this->routing_rules, $this->Routing->route_post( self::ACTION_VALUE ) );
    }

    private function ajax_spot_module( $routing, $module ) {

        if ( isset( $routing['_'.$module] ) ) {

            if ( $module == 'box' ) {
                if ( isset( $routing['_box'] ) && is_array( $routing['_box'] ) )
                    $this->ajax_spot_module( $routing['_box'], $this->Routing->route_post( self::BOX_ACTION ) );
                return;
            }

            if ( $this->haveExtendsDeclaration( $routing['_'.$module] ) ){
                $this->extendsResourceAndRights( $routing['_'.$module] );
            }

            $this->moduleResource = $routing['_'.$module]['resource'];
            $this->module_name = $module;

            if ( isset( $routing['_'.$module]['rulesname'] ) )
                $this->module_rights = $routing['_'.$module]['rulesname'];
        }
    }
}