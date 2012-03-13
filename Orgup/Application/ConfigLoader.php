<?php

namespace Orgup\Application;
use Orgup\Application\Exception\Config;
use \Orgup\Application\ConfigLoader\YMLLoader;
use \Orgup\Common\Localization;

interface MainLoaderInterface {
    public function get_localization( $lang );
    public function get_routing_rules();
    public function get_expansions();
}

class ConfigLoader implements MainLoaderInterface {

    protected $main_config_path = 'configs/main.yml';
    protected $routing_path = 'configs/routing.yml';
    protected $expansions_path = 'configs/expansion.yml';

    protected $routing_rules = array();
    protected $configLoader;
    protected $mainConfig;
    protected $expansions = array();

    protected $Registry;

    public function __construct() {

        Logger::log( 'Run Application', __FILE__, __LINE__ );

        $this->Registry = Registry::instance();

        $this->configLoader = YMLLoader::getLoader();
        Logger::log('Load main config');
        $this->load_mainConfig();
        $this->set_to_registry_mainConfig();
        Logger::log('Load routing');
        $this->load_routing_rules();
        $this->set_to_registry_mainClasses();
        Logger::log('Load Expansions config');
        $this->expansions = $this->configLoader->loadFile( ROOTDIR.$this->expansions_path );
        Logger::log('Load DB layer');
        $this->load_DBLayer();
        mb_internal_encoding('UTF-8');
        Logger::log('Configuration created');
    }

    private function load_mainConfig() {
        $this->mainConfig = $this->configLoader->loadFile( ROOTDIR.$this->main_config_path );
        $this->checkDB();
        $this->checkPath();
    }

    private function checkDB() {

        if (!isset( $this->mainConfig['db_access'] ) )
            throw
                new Config\ConfigIsBreak('DB access not found in config');
    }

    private function checkPath() {

        if (!isset( $this->mainConfig['HOST'] ) )
            throw
                new Config\ConfigIsBreak('Variable "HOST" not found in config');
    }

    private function set_to_registry_mainConfig() {

        foreach ( $this->mainConfig as $key => $value )
            $this->Registry->set( $key, $value );
    }

    private function load_routing_rules() {
        $this->routing_rules = $this->configLoader->loadFile( ROOTDIR.$this->routing_path );
    }

    private function set_to_registry_mainClasses() {
        $Routing = new Routing();
        $this->Registry->set('Routing', $Routing );
        $this->Registry->set('Path', new Path( $Routing ) );
    }

    private function load_DBLayer() {

        $classLoader = new ClassLoader('Doctrine', ROOTDIR.'system');
        $classLoader->register();

        $config = new \Doctrine\DBAL\Configuration;
        $config->setSQLLogger( new OrgupSQLLogger );

        $db_config = $this->Registry->get('db_access');

        if(defined('ORGUP_TEST'))
        {
            $db_config = $this->Registry->get('db_access_test');
        }

        $this->Registry->set( 'db', \Doctrine\DBAL\DriverManager::getConnection($db_config , $config ) );
    }

    public function get_routing_rules() {
        return $this->routing_rules;
    }

    public function get_localization( $lang ) {
        static $locale;
        if ( $locale !== null )
            return $locale;
        return $locale = new Localization($this, $this->load_locale( $lang ), $lang);
    }

    public function load_locale( $locale ) {
        return $this->configLoader->loadFile( ROOTDIR.'Orgup/Locale/'.$locale.'.yml' );
    }

    public function get_expansions()
    {
        return $this->expansions;
    }
}