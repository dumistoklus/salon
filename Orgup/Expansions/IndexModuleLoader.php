<?php
namespace Orgup\Expansions;
use Orgup\Application\ModuleLoader;

class IndexModuleLoader extends ModuleLoader {

    protected $defaultDataClass = '\Orgup\DataModels\IndexData';
    protected $emptyModule = '\Orgup\Modules\IndexModuleBuilder';

    public function get_module_templates() {
        if ( isset( $this->resource['templates'] ) )
        {
            return $this->resource['templates'];
        }

        return array();
    }

    public function get_module_scripts_and_styles() {
        if ( isset( $this->resource['scripts'] ) ) {
            return $this->resource['scripts'];
        }

        return array();
    }

    protected function loadModuleObject( $moduleName ) {
        parent::loadModuleObject( $moduleName );

        if ( method_exists( $this->Module, 'setWayPattern' ) )
            $this->Module->setWayPattern( $this->SpotModule->get_module_pattern() );
    }
}