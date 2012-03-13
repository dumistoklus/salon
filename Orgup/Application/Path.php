<?php

namespace Orgup\Application;

interface LadderOfPath {
    public function get_step_of_ladder( $step );
    public function get_full_path();
    public function add_param( $param, $value, $path );
    public function delete_param( $param, $path );
}

class PathIsIncorrect extends \Exception{}

class Path implements LadderOfPath {

    private $ladder = array();
    private $full_path;

    public function __construct( $Routing ) {

        $this->full_path = $this->make_full_path( $Routing );
        $parsed_url = $this->parse_url( $this->full_path );

        $this->ladder = $parsed_url['ladder'];
    }

    // возвращаем ступень лестницы
    public function get_step_of_ladder( $step ) {

        if ( isset( $this->ladder[$step] ) )
            return $this->ladder[$step];

        return FALSE;
    }

    public function get_full_path() {
        return $this->full_path;
    }

    public function get_ladder_path() {

        return '/'.implode( '/', $this->ladder );
    }

    public function get_paths_size()
    {
        $uri = $this->get_ladder_path();
        $size = substr_count($uri, '/');

        return ($uri{strlen($uri) - 1} == '/') ? $size - 1 :  $size;
    }

    public function delete_param( $param, $input_path = null ) {

        $path = ( $input_path === null ) ? $this->full_path : $input_path;

        if ( !mb_stripos( $path, $param ) )
            return $path;

        $parsed_url = $this->parse_url( $path );
        $parsed_query = array();
        $path_before_query = $parsed_url['path'];
        parse_str( $parsed_url['query'], $parsed_query );
        unset( $parsed_query[$param] );

        $get_string = array();
        if ( !empty( $parsed_query ) )
            foreach ( $parsed_query as $key => $value )
                $get_string[] = $key.'='.$value;

        $output_path = $path_before_query.( !empty( $get_string ) ? '?' : '').implode( '&', $get_string );

        if ( $input_path === null ) {
            $this->full_path = $output_path;
        }

        return $output_path;
    }

    public function add_param( $param, $value, $path = null ) {

        if ( is_null( $path ) )
            $path = $this->full_path;

        if ( substr_count( $path, '?' ) ) {

            // ищем в тексте такую же переменную
            $newpath = preg_replace( "/(\b".$param."=[-_0-9a-z]*\b)/is", $param.'='.$value, $path, -1, $count );

            // если путь не был изменен, то добавляем пареметр и значение
            if ( !$count )
                return $path. '&' .$param.'='.$value;
            return $newpath;

        } else {
            return ( mb_substr( $path, mb_strlen($path) - 1, 1 ) != '/' ) ? $path.'/?'.$param.'='.$value : $path.'?'.$param.'='.$value;
        }
    }

    private function parse_url( $url ) {

        $parsed_url = parse_url( $url );

        // fix
        if ( $parsed_url === false ) {

            $position = mb_strpos( $url, '?' ) ?: 0;
            $parsed_url = array(
                'query' => mb_substr( mb_strstr( $url, '?') ?: '', 1 ),
                'path'  => mb_substr( $url, 0, $position )
            );
        }

        $parsed_url['ladder'] = explode( '/', $parsed_url['path'] );

        array_shift( $parsed_url['ladder'] );
        // if last step of ladder is empty
        if ( !empty( $parsed_url['ladder']) && empty( $parsed_url['ladder'][count( $parsed_url['ladder'] ) - 1]) )
            unset( $parsed_url['ladder'][count($parsed_url['ladder'] ) - 1] );

        return $parsed_url;
    }

    private function make_full_path( $Routing ) {

        if ( $Routing instanceof Routing )
            $full_path = $Routing->route_server('REQUEST_URI');
        elseif ( is_string( $Routing ) ) {
            $full_path = $Routing;
        } else {
            throw new PathIsIncorrect();
        }

        return $full_path;
    }
}