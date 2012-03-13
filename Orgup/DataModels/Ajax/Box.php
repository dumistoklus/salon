<?php

namespace Orgup\DataModels\Ajax;
use Orgup\DataModels\AjaxData;

class Box extends AjaxData {

    private $header_text;
    protected $box_id;
    protected $box_size;
    private $_overlay = false;

    public function postInit() {

        $this->box_id = (string)$this->Routing()->route_post('boxid');

        array_unshift( $this->js,
            array( 'function_name' => 'render_box',
                'parameters' => array(
                    'header' => array(
                         'translate_it' => true,
                         'type'         => 'boxheaders',
                         'param'        => $this->header_text
                    ),
                    'boxid' => $this->box_id,
                    'boxsize' => $this->box_size,
                    'overlay' => $this->_overlay
                )
            )
        );
    }

    protected function overlay_on() {
        $this->_overlay = true;
    }

    protected function close_this_box() {
        $this->add_js('close_box', array('boxid' => $this->box_id ) );
    }

    protected function set_box_size( $size ) {
        $this->box_size = $size;
    }

    public function set_header( $header ) {
        $this->header_text = $header;
    }

    public function get_header() {
        return $this->header_text;
    }
}