<?php

namespace Orgup\Application;
use \Orgup\Expansions\Ways;
use \Orgup\Application\Exception\Module;
use \Orgup\Modules\EmptyModule;
use \Orgup\Application\Exception\Expansion\ExpansionConfigIsBreak;

abstract class ModuleLoader {

    protected $SpotModule;

    /**
     * @var \Orgup\Modules\ModuleBuilder;
     */
    protected $Module;
    /**
     * @var \Orgup\DataModels\Data
     */
    protected $Data;
    protected $Ways;

    protected $defaultDataClass = '\Orgup\DataModels\Data';



    protected $emptyModule = '\Orgup\Modules\EmptyModule';
    protected $resource;
    protected $module_name;

    protected $path_to_data_models;
    protected $path_to_modules;

    public function __construct(
        SpotModule $SpotModule,
        Ways $Ways
    ) {
        Logger::log('Init ModuleLoader', __FILE__, __LINE__);
        $this->SpotModule = $SpotModule;
        $this->Ways = $Ways;

        $this->path_to_data_models = ROOTDIR.'Orgup/';
        $this->path_to_modules = ROOTDIR.'Orgup/';

        $this->module_name = $SpotModule->get_module_name();
        $this->resource = $SpotModule->get_resource();
    }

    public function init() {
        try {
            Logger::log('Init Module Expansions');
            $this->initExpansions();

            if ( is_null( $this->resource ) ) {
                Logger::log('Resource not found '.$this->SpotModule->get_module_name());
                $this->load_another_module('404');
            }

            if ( $this->checkRights() ) {
                $this->initData();
                $this->initModule();
            }
            else {
                $this->load_another_module('0nothaverights');
                $this->init();
            }
        }
        catch ( Module\E404 $e )
        {
            $this->run404();
        }
    }
    /**
     * @return \Orgup\Modules\ModuleBuilder
     */
    public function getModule()
    {
        return $this->Module;
    }

    protected function run404()
    {
        $this->load_another_module('404');
        $this->init();
        $this->runModule();
    }

    protected function load_another_module( $module_name ) {

        Logger::log("Load another module");

        if ( $this->module_name == $module_name ) {
            throw new ConfigIsWrong('Attempt to load the module with the same name '.$module_name );
        }

        $this->module_name = $module_name;
        $this->SpotModule->find_another_module( $module_name );
        $this->resource = $this->SpotModule->get_resource();
    }

    public function runModule()
    {
        try
        {
            Logger::log( 'Pre run of module '.$this->module_name, __FILE__, __LINE__ );
            $this->Module->actionsBeforeRun();
            Logger::log( 'Run module '.$this->module_name, __FILE__, __LINE__ );

            $this->disableMySQLCache();

            $this->Module->run();

            $this->logMySQLReadOperations();

            Logger::log( 'After run of module '.$this->module_name, __FILE__, __LINE__ );
            $this->Module->actionsAfterRun();
        }
        catch ( Module\E404 $e )
        {
            $this->run404();
        }
    }

    private function initExpansions()
    {
        $expansions = $this->SpotModule->getExpansionsSettings();
        $moduleExpansions = $this->SpotModule->getExpansion();

        if(empty($moduleExpansions)) return true;

        $Handlers = array();

        foreach($moduleExpansions as $type=>$module)
        {
            foreach($module as $module=>$param)
            {
                if(!isset($expansions[$type]['expansions'][$module]))
                {
                    throw new ExpansionConfigIsBreak('expansions.yml config is bad. Check it. Type `'.$type.'` not isset OR not isset `expansions` OR type `'.$module.'` not isset');
                }

                Logger::log('Add expansion: ' . $type.' -> '.$module.' | ' .$expansions[$type]['expansions'][$module]. ' & params : ' . $param);
                $Handlers[$expansions[$type]['handler']][] = array('module' => $expansions[$type]['expansions'][$module], 'param' => $param);
            }
        }

        foreach($Handlers as $handlerClass => $params)
        {
            $Handler = new $handlerClass($params);
            $Handler->run();
        }
    }

    private function disableMySQLCache()
    {
        if(Registry::instance()->get('debug'))
        {
            $db = Registry::instance()->get('db');

            $db->executeQuery('SET query_cache_type = OFF');
            $db->executeQuery('FLUSH STATUS');
        }
    }

    private function logMySQLReadOperations()
    {
        if(Registry::instance()->get('debug'))
        {
            $db = Registry::instance()->get('db');

            $result = $db->fetchAll("SHOW SESSION STATUS LIKE 'handler_%'");

            $handlers = array();

            foreach($result as $value)
            {
                $handlers[$value['Variable_name']] = (int) $value['Value'];
            }

            if($handlers['Handler_read_key'] < $handlers['Handler_read_rnd'] || $handlers['Handler_read_key'] < $handlers['Handler_read_rnd_next'])
            {
                Logger::err('Handler_read_key : '.$handlers['Handler_read_key']);
            }
            else
            {
                Logger::good('Handler_read_key : '.$handlers['Handler_read_key']);
            }

            Logger::err('Handler_read_rnd : '.$handlers['Handler_read_rnd']);
            Logger::err('Handler_read_rnd_next : '. $handlers['Handler_read_rnd_next']);
            Logger::err('Handler_read_first : '. $handlers['Handler_read_first']);
            Logger::err('Handler_read_next : '. $handlers['Handler_read_first']);

        }
    }

    /**
     * @return \Orgup\DataModels\Data
     */
    public function getData() {

        return $this->Data;
    }



    protected function initData() {

        if ( !isset( $this->resource['data'] ) ) {
            $this->loadDataObject( $this->defaultDataClass );

        } else if ( class_exists($this->resource['data']) ) {
            $this->loadDataObject( $this->resource['data'] );

        } else {
            throw new Module\DataClassNotFound( $this->resource['data'] );
        }
    }

    public function defaultDataClass() {
        return $this->defaultDataClass;
    }

    public function defaultModuleClass()
    {
        return $this->emptyModule;
    }

    protected function loadDataObject( $ModelName ) {

        Logger::log( 'Init DataClass '.$ModelName, __FILE__, __LINE__ );
        $this->Data = new $ModelName();
        if ( Registry::instance()->get('debug') )
            $this->Data->setDebug( true );
    }

    protected function initModule() {

        Logger::log('Load module');

        if( !isset( $this->resource['module'] ) ) {
            $this->loadModuleObject( $this->emptyModule );
        }
        else if ( class_exists( $this->resource['module'] ) )  {
            $this->loadModuleObject( $this->resource['module'] );
        }
        else {
           throw new Module\ModuleNotFound( $this->module_name );
        }
    }

    protected function loadModuleObject( $moduleName ) {

        Logger::log( 'Init Module '.$moduleName, __FILE__, __LINE__ );
        $this->Module = new $moduleName( $this->Data, Registry::instance()->get('Routing'), $this->Ways );
    }

    protected function checkRights() {

        Logger::log('Check rights');
        $right_name = $this->SpotModule->get_module_rights();

        if ( $right_name === null )
            return true;

        $User = \Orgup\Application\Registry::instance()->User();
        if ( !$User->imember() ) {
            $this->ifUserNotMemberAndNeedRights();
        }

        return $User->check_this_right( $right_name );
    }

    protected function ifUserNotMemberAndNeedRights() {
        throw new Redirect( $this->Ways->add('return', urlencode( $this->Ways->thispage() ), $this->Ways->login() ) );
    }
}