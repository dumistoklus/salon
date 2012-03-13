<?php

namespace Orgup\Modules\Index\Administration;

use \Orgup\Application\Registry;
use \Orgup\Plugins\VersionController\VersionControllerUpdater;

class Site extends AdministrationModuleBuilder {

    public function run() {

        // обновление стилей
        if ( $this->Routing->route_post('reload_styles') ) {

            $StylesVersionControllerUpdater = new VersionControllerUpdater('css', ROOTDIR.'configs/styles.yml', ROOTDIR.'configs/orgup_styles.yml', '/style/converted');

            if(!$StylesVersionControllerUpdater->run()) {
                $this->Data->add_error('not_update_config', 'admin');
                $this->Data->add_error( $StylesVersionControllerUpdater->getErrors(), 'admin');
            }else{
                if($StylesVersionControllerUpdater->isActual())
                    $this->Data->add_notification( 'config_actual', 'admin');
                else
                    $this->Data->add_notification( 'updated', 'admin');
            }

        }

         // обновление скриптов
        if ( $this->Routing->route_post('reload_scripts') ) {
           $ScriptsVersionControllerUpdater = new VersionControllerUpdater('js', ROOTDIR.'configs/scripts.yml', ROOTDIR.'configs/orgup_scripts.yml', '/js/converted');

            if(!$ScriptsVersionControllerUpdater->run()) {
                $this->Data->add_error('not_update_config', 'admin');
                $this->Data->add_error( $ScriptsVersionControllerUpdater->getErrors(), 'admin');
            }else{
                if($ScriptsVersionControllerUpdater->isActual())
                    $this->Data->add_notification( 'config_actual', 'admin');
                else
                    $this->Data->add_notification( 'updated', 'admin');
            }

        }

        if($this->Routing->route_post('flush_twig'))
        {
            $this->flushdir(ROOTDIR.'twig_tmp');
            $this->Data->add_notification( 'twig_flushed', 'admin');
        }

        $StylesVC = new VersionControllerUpdater('css', ROOTDIR.'configs/styles.yml', ROOTDIR.'configs/orgup_styles.yml', '/style/converted');
        $ScriptsVC = new VersionControllerUpdater('js', ROOTDIR.'configs/scripts.yml', ROOTDIR.'configs/orgup_scripts.yml', '/js/converted');


        $this->Data->setStylesLastUpdateDate($StylesVC->lastUpdateConfig());
        $this->Data->setScriptsLastUpdateDate($ScriptsVC->lastUpdateConfig());
    }

    private function flushdir($dirname,$oc=0)
    {
        // Sanity check
        if (!file_exists($dirname)) {
            return false;
        }
        // Simple delete for a file
        if (is_file($dirname)) {
            return unlink($dirname);
        }
        // Loop through the folder
        if(is_dir($dirname))
        {
            $dir = dir($dirname);
            while (false !== $entry = $dir->read()) {
                // Skip pointers
                if ($entry === '.' || $entry === '..') {
                    continue;
                }
                // Recurse
                $this->flushdir($dirname.'/'.$entry, 1);
            }
            $dir->close();
        }
        // Clean up
        if ($oc==1)
        {
            return rmdir($dirname);
        }
    }
}