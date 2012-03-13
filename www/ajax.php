<?php
define( 'ROOTDIR', realpath( __DIR__.'/../' ).'/' );

use Orgup\Expansions\AjaxSpotModule;
use Orgup\Expansions\AjaxConfigLoader;
use Orgup\Application\Registry;
use Orgup\Common\User;
use Orgup\Expansions\AjaxModuleLoader;
use Orgup\Expansions\Ways;

require ROOTDIR.'Orgup/Application/ClassLoader.php';
$classLoader = new Orgup\Application\ClassLoader('Orgup', ROOTDIR );
$classLoader->register();

$ConfigLoader = new AjaxConfigLoader();
$app = new Orgup\Application\Application();

$app->before(function() {
    Registry::instance()->set('User', new User );
});
$app->after(function() {
    Registry::instance()->User()->saveData();
});

$app->app( function() use ( $ConfigLoader) {

    // run
    $Ways = new Ways();
    $SpotModule = new AjaxSpotModule( $ConfigLoader, Registry::instance()->get('Routing') );
    $ModuleLoader = new AjaxModuleLoader( $SpotModule, $Ways );
    \Orgup\Application\Rights::instatnce( $SpotModule->get_rights() );

    $ModuleLoader->init();
    $ModuleLoader->runModule();

    // output
    $Data = $ModuleLoader->getData();
    $Data->set_locale( $ConfigLoader->get_localization( Registry::instance()->User()->getLocale() ) );

    $Templator = new Orgup\Expansions\AjaxTemplator();
    $Outputer = new Orgup\Expansions\AjaxOutputer( $Templator, $Data, $Ways );

    echo $Outputer->get_output();
});

$app->run();