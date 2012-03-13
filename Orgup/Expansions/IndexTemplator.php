<?php

namespace Orgup\Expansions;
use Orgup\Application\Templator;
use Orgup\Application\Logger;

interface ThemeInterface {
    public function set_header_template( $template_name );
    public function get_header_template();
    public function set_footer_template( $template_name );
    public function get_footer_template();
    public function set_main_template( $template_name );
    public function get_main_template();
}

class MainThemeNotEstablished extends \Exception {}

class IndexTemplator extends Templator implements ThemeInterface {

    private $header_template = 'main/header.htm';
    private $footer_template = 'main/footer.htm';
    private $main_template;

    public function __construct( $templates ) {
        if ( isset( $templates['main'] ) )
        {
            Logger::log('Load main template: '.$templates['main']);
            $this->set_main_template( $templates['main'] );
        }
        if ( isset( $templates['header'] ) )
        {
            Logger::log('Load header template: '.$templates['header']);
            $this->set_header_template( $templates['header'] );
        }
        if ( isset( $templates['footer'] ) )
        {
            Logger::log('Load header footer: '.$templates['footer']);
            $this->set_footer_template( $templates['footer'] );
        }
    }

    public function set_header_template( $template_name ) {
        $this->header_template = $template_name;
    }

    public function get_header_template() {
        return $this->header_template;
    }

    public function set_footer_template( $template_name ) {
        $this->footer_template = $template_name;
    }

    public function get_footer_template() {
        return $this->footer_template;
    }

    public function set_main_template( $template_name ) {
        $this->main_template = $template_name;
    }

    public function get_main_template() {
        if ( !is_null( $this->main_template ) )
            return $this->main_template;
        throw new MainThemeNotEstablished('Establish main theme!');
    }
}