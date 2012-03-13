<?php

namespace Orgup\DataModels;
use Orgup\Application\Registry;

interface IndexDataInterface {

    public function get_styles();
    public function get_scripts();
    public function add_style( $style );
    public function add_script( $script );
    public function add_title( $title );
    public function set_keywords( $keywords );
    public function set_description( $description );
    public function get_title();
    public function get_keywords();
    public function get_description();
    public function script_text();
    public function exist_script_text();
}

class IndexData extends Data implements IndexDataInterface {

    protected $styles = array();
    protected $scripts = array();

    protected $js_text = array(
        'vars'      => array(),
        'pages'     => array(),
        'common'    => array(),
        'templates' => array()
    );

    protected $title = array();
    protected $keywords;
    protected $description;

    protected $paginator;
    private $lang_added_to_scripts = false;

    protected function init_styles()
    {
        $this->add_style('content');
        $this->add_style('index');
    }

    /**
     * @param $style
     * @return void
     */
    public function add_style( $style ) {
        if ( is_string( $style ) )
            array_push( $this->styles, $style );
    }

    /**
     * @return array
     */
    public function get_styles() {
        return $this->styles;
    }

    /**
     * возвращает набор ключей для скриптов
     * @param $script
     * @return void
     */
    public function add_script( $script ) {
        $this->scripts[] = $script;
    }

    /**
     * @return array
     */
    public function get_scripts() {
        return $this->scripts;
    }

    /**
     * @return bool|string
     */
    public function script_text() {

        if ( !$this->exist_script_text() )
            return false;

        return 'var orgup='.str_replace( '/', '\/', $this->set_value_for_replace( $this->js_text ).';' );
    }

    /**
     * @return bool
     */
    public function exist_script_text() {

        if ( !$this->lang_added_to_scripts ) {
            $this->add_lang_to_scripts();
            $this->lang_added_to_scripts = true;
        }

        if ( $this->debug )
            $this->js_text['vars']['debug'] = 1;

        return( !empty( $this->js_text ) );
    }

    /**
     * @param $title
     * @return void
     */
    public function add_title( $title ) {
        $this->title[] = $title;
    }

    /**
     * @param $keywords
     * @return void
     */
    public function set_keywords( $keywords ) {
        $this->keywords = $keywords;
    }

    /**
     * @param $description
     * @return void
     */
    public function set_description( $description ) {
        $this->description = $description;
    }

    /**
     * @return string or null
     */
    public function get_title() {

        $title = array();

        if ( !empty( $this->lang ) ) {
            foreach ( $this->title as $tit ) {
                if ( isset( $this->lang[$tit]['TITLE'] ) ) {
                    $title[] = $this->lang[$tit]['TITLE'];
                } else if ( !empty( $tit ) ) {
                    $title[] = $tit;
                }
            }
        }

        return $title;
    }

    /**
     * @return sting
     */
    public function get_keywords() {
        return $this->keywords;
    }

    /**
     * @return string
     */
    public function get_description() {
        return $this->description;
    }

    /**
     * @param $names
     * @param string $error_type
     * @return void
     */
    public function add_form_error_by_field_name($names, $error_type = null )
    {
        $this->add_script_var('form_errors', $names);

        if($error_type !== null)
        {
            $this->add_error($names, $error_type);
        }
        else
        {
            $this->add_error('WRONG_DATA', 'form');
        }
    }

    /**
     * @param $key
     * @param $value
     * @return void
     */
    public function add_script_var( $key, $value ){
        $this->js_text['vars'][$key] = $value;
    }

    public function run_script( $value ) {
        $this->js_text['vars']['scripts'][] = $value;
    }

    /**
     * @param $total
     * @param $current_page
     * @param $limit
     * @param int $mid_range
     * @return void
     */
    public function add_paginator( $total, $current_page, $limit, $mid_range = 6 ) {
        if ( $total > $limit )
            $this->paginator = new \Orgup\Common\Paginator( $total, $current_page, $limit, $mid_range );
    }

    /**
     * @return object Paginator
     */
    public function getPaginator() {
  
        return $this->paginator;
    }

    /**
     * @return bool
     */
    public function thisPageIs() {

        $args = func_get_args();

        // полный путь, часть которго может быть неучтена
        $full_path = Registry::instance()->get('Path')->get_full_path();
        // тот путь, который точно должен быть учтен
        $strong_path = Registry::instance()->get('Path')->get_ladder_path();
        $length_of_strong = mb_strlen( $strong_path );

        $host = Registry::instance()->get('HOST');

        foreach ( $args as $arg ) {

            // $arg может содержать хост, поэтому режем его и заменяем на слеш
            if ( mb_substr_count( $arg, $host ) > 0 AND $host != '/' )
                $arg = str_replace( $host, '/', $arg );

            $length_of_arg = mb_strlen( $arg );
            $length_of_cut = $length_of_arg > $length_of_strong ? $length_of_arg : $length_of_strong;
            $path = mb_substr( $full_path, 0, $length_of_cut );

            if ( $path == $arg )
                return true;
        }
        return false;
    }

    /**
     * @param $key
     * @param $value
     * @return void
     */
    protected function add_script_text( $key, $value ) {
        $this->js_text = array_merge_recursive( $this->js_text, array( $key => $value ) );
    }

    /**
     * @param $value
     * @return string
     */
    protected function set_value_for_replace( $value ) {

        $output = '';

        if ( is_array( $value ) ) {

            foreach ( $value as $key2 => $value2 ) {
                if ( !empty( $output ) )
                    $output .= ',';

                $output .= $key2.':'.$this->set_value_for_replace( $value2 );
            }

            $output = '{'.$output.'}';

        } elseif ( is_string( $value ) ) {
            $output = '"'.$value.'"';
        } elseif ( is_int( $value ) || is_float( $value ) ) {
            $output = $value;
        }

        return $output;
    }

    protected function add_lang_to_scripts() {
        if ( isset( $this->lang['scripts'] ) )
            $this->add_script_text('lang', $this->lang['scripts'] );
    }

    public function get_main_menu() {

        static $cats;

        if ( $cats === null ) {

        $db = Registry::instance()->get('db');
        $catalog = $db->fetchAll('SELECT * FROM `catalogs` ORDER BY `name`');
            $cats = $this->catalog_sort( $catalog );
        }

        return $cats;
    }

    protected function catalog_sort( $from_bd ) {

        $catalog = array();

        foreach ( $from_bd as $cat ) {
            $catalog[$cat['parent']][] = $cat;
        }

        return $catalog;
    }
}
