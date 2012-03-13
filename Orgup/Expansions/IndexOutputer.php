<?php

namespace Orgup\Expansions;
use \Orgup\Application\Exception\Config\ConfigIsBreak;
use \Orgup\Application\Logger;

class IndexOutputer extends \Orgup\Application\Outputer {

    private $template_engine;
    private $Templator;
    private $Data;
    private $Ways;

    protected $required_styles = array(
        'common'
    );

    protected $required_scripts = array(
        'jquery',
        'core',
        'main'
    );

    public function __construct( IndexTemplator $Templator, \Orgup\DataModels\IndexData $Data, Ways $Ways ) {

        Logger::log('Rending page', __FILE__, __LINE__);

        $this->Templator = $Templator;
        $this->Data = $Data;
        $this->Ways = $Ways;

        $this->init_styles_and_scripts();
    }

    public function get_output() {
        Logger::log('Start of Output', __FILE__, __LINE__);
        $this->template_engine = $this->Templator->get_template_engine();

        return $this->render_header().
               $this->render_main_template().
               $this->render_footer();
    }

    private function render_header() {
        $header = $this->template_engine->loadTemplate( $this->Templator->get_header_template() );
        return $header->render( array(
                                     'data'    => $this->Data,
                                     'ways'    => $this->Ways,
                                     'lang'    => $this->Data->getLang(),
                                     'scripts' => $this->scripts,
                                     'styles'  => $this->styles
                                ) );
    }

    private function render_footer() {
        $footer = $this->template_engine->loadTemplate( $this->Templator->get_footer_template() );
        return $footer->render( array( 'data' => $this->Data, 'ways' => $this->Ways, 'lang' => $this->Data->getLang() ) );
    }

    private function render_main_template() {
        $main = $this->template_engine->loadTemplate( $this->Templator->get_main_template() );
        return $main->render( array( 'data' => $this->Data, 'ways' => $this->Ways, 'lang' => $this->Data->getLang() ) );
    }

    protected function init_styles_and_scripts() {

        $scripts_keys = $this->merge_keys( $this->required_scripts, $this->Data->get_scripts() );
        $styles_keys = $this->merge_keys( $this->required_styles, $this->Data->get_styles() );

        // формируем полные массивы путей
        if ( !empty( $scripts_keys ) ) {
            $this->scripts = $this->get_array_from_file( $scripts_keys, ROOTDIR.self::SCRIPTS_VERSION, ROOTDIR.self::SCRIPTS );
        }

        if ( !empty( $styles_keys ) ) {
            $this->styles = $this->get_array_from_file( $styles_keys, ROOTDIR.self::STYLES_VERSION, ROOTDIR.self::STYLES );
        }
    }
}

