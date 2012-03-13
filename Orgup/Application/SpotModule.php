<?php

namespace Orgup\Application;
use \Orgup\Common\UserAuthDataValidator;

interface SpotModuleInterface {
    public function get_resource();
    public function get_module_name();
    public function find_another_module( $module_name );
    public function get_rights();
    public function get_module_rights();
}

class SpotModule implements SpotModuleInterface {

    protected $moduleResource;
    protected $routing_rules;
    protected $module_name;
    protected $module_rights;
    protected $expansions_settings = array();
    protected $expansions = array();
    protected $definedModule;

    private $Path;
    private $module_pattern;
    private $max_ladder_level = 0;

    public function __construct( MainLoaderInterface $config, Path $Path ) {

	    Logger::log('Init Spot', __FILE__, __LINE__);
        $this->routing_rules = $config->get_routing_rules();
        $this->expansions_settings = $config->get_expansions();
        $this->Path = $Path;

        Registry::instance()->set('SpotModule', $this);

        $this->spot_main_module_name( $this->routing_rules );

        if ( $this->isPathsCountMoreThanMaxSpottedPathsOnRouting() ) {
            $this->reset_module();
        }
    }

    /**
     * @return null or array
     */
    public function get_resource() {
        return $this->moduleResource;
    }

    /**
     * @return null or array
     */
    public function get_module_name() {
        return $this->module_name;
    }

    /**
     * @param $module_name
     * @return null or array
     */
    public function find_another_module( $module_name ) {

        $this->reset_module();

        if ( isset( $this->routing_rules['_'.$module_name ]['resource'] ) ) {
            $this->set_module_resource_and_rules( $this->routing_rules['_'.$module_name ], $module_name );
        }
    }

    public function get_module_pattern() {
        return $this->module_pattern;
    }

    /**
     * @return string
     */
    public function get_module_rights() {
        return $this->module_rights;
    }

    /**
     * @return array
     */
    public function get_rights() {
        static $modules_rights;
        if ( $modules_rights === null ) {
	        $rules = array();
            $modules_rights = array_keys( $this->load_modules_rights( $this->routing_rules, $rules ) );
        }

        return $modules_rights;
    }

    private function spot_main_module_name( &$routing_rules, $ladder_level = 0 ) {

        foreach ( $routing_rules as $module_name => &$module ) {

            $moduleFounded = false;
            $module_name = mb_substr( $module_name, 1 );

            if ( $this->haveExtendsDeclaration( $module ) ) {
                $this->extendsResourceAndRights( $module );
            }

            // проверка на путь
            if ( $this->havePathDeclaration( $module ) ) {
                if ( $this->pathIsTheWay( $module ) ) {
                    $moduleFounded = true;
                }
            }

            // проверка на паттерн
            elseif ( $this->havePatternDeclaration( $module ) ) {

                if ( $this->patternIsTheWay( $module, $ladder_level ) ) {
                    $moduleFounded = true;
                }
            }

            // проверка имени модуля
            elseif ( $this->moduleNameIsTheWay( $module_name, $ladder_level ) ) {
                $moduleFounded = true;
            }

            if ( $moduleFounded ) {

	            // внутренний редирект
	            if ( $this->has_module_url( $module ) ) {
		            $this->set_new_url( $module );
		            $this->spot_main_module_name( $this->routing_rules );
		            return;
	            }

                $this->set_module_resource_and_rules( $module, $module_name );
                $this->set_expansions( $module );

                $this->max_ladder_level = ( $this->max_ladder_level > $ladder_level ) ?: $ladder_level;

                if( $this->isAbsoluteParamEnabled() )
                {
                    break;
                }

                // проверка субмодулей
                if ( $this->haveSubmodules( $module, $ladder_level ) ) {
                    $this->spot_submodules( $module, $ladder_level );
                }

	            return;
            }
        }
    }

    protected function haveExtendsDeclaration( $module ) {
        return ( isset( $module['extends'] ) );
    }

    protected function extendsResourceAndRights(&$module)
    {
        $inheritedModule = $module['extends'];

        if( !isset( $this->routing_rules[$inheritedModule] ) )
            throw new ConfigIsWrong('Inherit module not found!');

        if( !isset( $module['resource'] ) )
            $module['resource'] = array();

        if( !isset( $module['rulesname'] ) && isset($this->routing_rules[$inheritedModule]['rulesname'] ) )
            $module['rulesname'] = $this->routing_rules[$inheritedModule]['rulesname'];

        $this->inherit( $this->routing_rules[$inheritedModule]['resource'], $module['resource'] );
    }

	private function has_module_url( $module ) {
		return isset( $module['page'] );
	}

	private function set_new_url( $module ) {

		$new_url = $module['page'];

		if ( $this->Path->get_full_path() == $new_url ) {
			throw new ConfigIsWrong('Redirecting to self');
		}

		$this->Path = new Path( $new_url );
		$this->reset_module();
	}

    private function inherit( $what, &$where )
    {
        foreach( $what as $key => $value )
        {
            if(is_array($value))
                if(isset($where[$key]))
                    $this->inherit($value, $where[$key]);
                else
                    $where[$key] = $value;

            if(!isset($where[$key]))
                $where[$key] = $value;
        }
    }

    private function havePathDeclaration( $module ) {
        return isset( $module['path'] );
    }

    private function havePatternDeclaration( $module ) {
        return isset( $module['pattern'] );
    }

    private function moduleNameIsTheWay( $module_name, $ladder_level ) {
        return ( $this->is_module_name( $module_name ) AND $this->get_ladder( $ladder_level ) == $module_name );
    }

    private function patternIsTheWay( $module, $ladder_level ) {

        $way = $this->get_ladder( $ladder_level );
        $pattern = $module['pattern'];

        if(substr_count($pattern,'f|user') > 0)
        {
            $pattern = str_replace('f|user', '('.UserAuthDataValidator::loginRegexp().')', $pattern);
        }

        if(substr_count($pattern, 'f|password') > 0)
        {
            $pattern = str_replace('f|password', '('.UserAuthDataValidator::passwordRegexp().')', $pattern);
        }

        if ( preg_match( '`^'.$pattern.'$`', $way, $matches ) ) {
            array_shift($matches);
            $this->module_pattern = $matches;
            return TRUE;
        }
        return FALSE;
    }

    private function spot_submodules( &$module, $ladder_level ) {

        ++$ladder_level;

        if ( $this->get_ladder( $ladder_level ) AND !is_null( $this->moduleResource ) ) {
            $this->spot_main_module_name( $module['submodules'], $ladder_level );
        }
    }

    private function haveSubmodules( $module, $ladder_level ) {
        return isset( $module['submodules'] ) AND is_array( $module['submodules'] ) AND $this->get_ladder( $ladder_level );
    }

    private function set_module_resource_and_rules( $module, $module_name ) {

        $this->definedModule = $module;

        if ( isset( $module['resource'] ) ) {
            $this->module_name = $module_name;
            $this->moduleResource = $module['resource'];
            if ( isset( $module['rulesname'] ) )
                $this->module_rights = $module['rulesname'];
        } else
            throw new ConfigIsWrong();
    }

    private function set_expansions($module)
    {
        if(isset ($module['expansion']) )
        {
            $this->expansions = $module['expansion'];
        }
    }

    private function is_module_name( $module_name ) {
        return preg_match( '/^[_a-z]{3,40}$/', $module_name );
    }

    private function get_ladder( $ladder_level ) {
        return $this->Path->get_step_of_ladder( $ladder_level );
    }

    private function pathIsTheWay( $module ) {
        return $module['path'] == $this->Path->get_ladder_path();
    }

    private function isPathsCountMoreThanMaxSpottedPathsOnRouting()
    {
        if($this->isAbsoluteParamEnabled()) return false;

        $count_of_paths = $this->Path->get_paths_size();
        if($count_of_paths > $this->max_ladder_level + 1)
        {
            Logger::err('Count of paths more than module can have');
            return true;
        }
        else
        {
            return false;
        }
    }
    
    private function reset_module()
    {
        $this->moduleResource = null;
        $this->module_rights = null;
        $this->module_name = null;
        $this->expansions = array();
    }

    private function load_modules_rights( $routing_rules, &$existing_rights) {

        foreach ( $routing_rules as $value ) {

            if ( isset( $value['rulesname'] ) ) {
                $existing_rights[$value['rulesname']] = 0;
            }
            if ( isset( $value['submodules'] ) ) {
                $this->load_modules_rights( $value['submodules'], $existing_rights );
            }
        }

        return $existing_rights;
    }

    private function isAbsoluteParamEnabled()
    {
        if(isset($this->definedModule['absolute']))
        {
            return $this->definedModule['absolute'];
        }

        return false;
    }

    public function getAllModules()
    {
        $modules = array();

        $this->addModule($this->routing_rules, $modules);

        return $modules;
    }

    private function addModule($modules, &$modules_list)
    {
        foreach($modules as $module)
        {
            if(isset($module['resource']['module']))
            {
                if(!in_array($module['resource']['module'], $modules_list))
                {
                    $modules_list[] = $module['resource']['module'];
                }
            }

            if(isset($module['submodules']))
            {
                $this->addModule($module['submodules'], $modules_list );
            }
        }
    }

	public function getExpansion()
	{
	    return $this->expansions;
	}

    public function getExpansionsSettings()
    {
        return $this->expansions_settings;
    }
}

class ConfigIsWrong extends \Exception {}