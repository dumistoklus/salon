<?php
define( 'ROOTDIR', realpath( __DIR__.'/../' ).'/' );

use Orgup\Application\SpotModule;
use Orgup\Application\ConfigLoader;
use Orgup\Application\Registry;
use Orgup\Common\User;
use Orgup\Expansions\IndexModuleLoader;
use Orgup\Expansions\Ways;
use \Orgup\Application\Logger;

require ROOTDIR.'Orgup/Application/ClassLoader.php';

$classLoader = new Orgup\Application\ClassLoader('Orgup', ROOTDIR );
$classLoader->register();

$ConfigLoader = new ConfigLoader();

$app = new Orgup\Application\Application();

$app->before(function() {
    Registry::instance()->set('User', new User );
});
$app->after(function() {
    Registry::instance()->User()->saveData();
});

$app->app( function() use ( $ConfigLoader ) {

    // run
    $Ways = new Ways();

    $SpotModule = new SpotModule( $ConfigLoader, Registry::instance()->get('Path') );

    $ModuleLoader = new IndexModuleLoader( $SpotModule, $Ways );
    \Orgup\Application\Rights::instatnce( $SpotModule->get_rights() );

    $ModuleLoader->init();
    $ModuleLoader->runModule();

    // output
    \Orgup\Application\Logger::log('Start Output');

    $Data = $ModuleLoader->getData();
    $Data->set_locale( $ConfigLoader->get_localization( Registry::instance()->User()->getLocale() ) );

    $Templator = new Orgup\Expansions\IndexTemplator( $ModuleLoader->get_module_templates() );

    $Outputer = new Orgup\Expansions\IndexOutputer( $Templator, $Data, $Ways );

    $ConfigLoader = null;
    echo $Outputer->get_output();
});

$app->run();