<?php

namespace Orgup\Application;

abstract class Templator {

    protected $template_engine;

    private function load_templator_loader() {

        require_once ROOTDIR.'system/Twig/Autoloader.php';
        \Twig_Autoloader::register();

        $mainloader = new \Twig_Loader_Filesystem(ROOTDIR.'templates/');

        $twig_params = array();

        if(Registry::instance()->get('twig_cache'))
        {
            $twig_params = array('cache' => ROOTDIR.'/twig_tmp', 'optimizations' => 1);
        }

        return $this->template_engine = new \Twig_Environment($mainloader, $twig_params);
    }

    /**
     * @return \Twig_Environment
     */
    public function get_template_engine() {

        if ( $this->template_engine )
            return $this->template_engine;

        return $this->load_templator_loader();
    }
}