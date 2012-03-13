<?php

namespace Orgup\DataModels;

class AjaxData extends Data {

	protected $js = array();            // храним название функции, которая выполниться если что
    private $status = false;
    private $cache = 0;               // время этого запроса в кэше браузера в секундах
    private $templates = array();     // шаблоны

    private $_scripts = array();       // скрипты, которые необходимо будет загрузить браузеру

    public function get_status() {
        return $this->status;
    }

    public function add_error( $error_text, $module_name = null ) {

        if ( $module_name === null )
            $module_name = 'ajax';

        return parent::add_error( $error_text, $module_name );
    }

    public function add_notification( $notification_text, $module_name = null ) {

        if ( $module_name === null )
            $module_name = 'ajax';

        return parent::add_notification( $notification_text, $module_name );
    }

    public function get_js() {
        return $this->js;
    }

    public function add_js( $js, $params = null  ) {
        $this->js[] = array( 'function_name' => $js, 'parameters' => $params );
    }

    public function set_status( $status ) {
        $this->status = $status;
    }

    /**
     * @param $names
     * @param string $error_type
     * @return void
     */
    public function add_form_error_by_field_name($names, $error_type = null )
    {
        if( $error_type !== null )
        {
            $this->add_error($names, $error_type );
        }
        else
        {
            $this->add_error('WRONG_DATA', 'form' );
        }

        if ( is_array( $names ) ) {
            $this->add_js('form_errors', $names );
        } else {
            $this->add_js('form_errors', array( 0 => $names ) );
        }
    }

    // in seconds
    public function set_cache_time( $cache ) {
        $this->cache = (int)$cache;
    }

    public function get_cache_time() {
        return $this->cache;
    }

    public function add_template( $template, $key = null ) {
        if ( $key ) {
            $this->templates[$key] = $template;
        } else {
            $this->templates[] = $template;
        }
    }

    public function get_templates() {
        return $this->templates;
    }

    public function add_script( $script ) {
        $this->_scripts[] = $script;
    }

    public function get_scripts() {
        return $this->_scripts;
    }

    public function postInit() {}
}