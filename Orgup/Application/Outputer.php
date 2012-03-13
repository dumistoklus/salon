<?php
namespace Orgup\Application;
use \Orgup\Application\ConfigLoader\YMLLoader;

class Outputer {

    const SCRIPTS = 'configs/scripts.yml';
    const STYLES = 'configs/styles.yml';
    const SCRIPTS_VERSION = 'configs/orgup_scripts.yml';
    const STYLES_VERSION = 'configs/orgup_styles.yml';

    protected $scripts = array();
    protected $styles = array();

    protected $required_styles = array();
    protected $required_scripts = array();

    protected function get_array_from_file( $keys, $base_config, $alt_config = null ) {

        // если скрипты и стили отключены - то сразу меняем на альтернативный вариант
        if ( $alt_config !== null && !\Orgup\Application\Registry::instance()->get('compressed_style_and_js') ) {
            $base_config = $alt_config;
            $alt_config = null;
        }

        $output_paths = array();

        $files = $this->get_config_from_file( $base_config );

        if ( empty( $files ) && $alt_config !== null ) {
            return $this->get_array_from_file( $keys, $alt_config );
        }

        foreach ( $keys as $key ) {

            if ( isset( $files[$key] )  ) {

                if ( isset( $files[$key]['file'] ) ) {
                    if ( !in_array( $files[$key]['file'], $output_paths ) )
                        $output_paths[] = $files[$key]['file'];
                } else {
                    \Orgup\Application\Logger::err( 'In file "'.$base_config.'" not found key "file" for key "'.$key.'"!' );
                }

            } else {

                // если в $base_config произошла ошибка - не используем его
                if ( $alt_config !== null ) {
                    \Orgup\Application\Logger::err('Loading original styles or scripts because there is an error in file "'.$base_config.'"', __FILE__, __LINE__ );
                    return $this->get_array_from_file( $keys, $alt_config );
                }

                \Orgup\Application\Logger::err('Style or script "'.$key.'" not exist', __FILE__, __LINE__ );
            }
        }

        return $output_paths;
    }

    protected function merge_keys( $a, $b ) {

        $c = array_merge( $a, $b );
        return array_unique( $c );
    }

    protected function get_config_from_file( $file ) {

        $Loader = YMLLoader::getLoader();
        return $Loader->loadFile( $file );
    }
}