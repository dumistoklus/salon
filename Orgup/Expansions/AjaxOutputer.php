<?php

namespace Orgup\Expansions;

class AjaxOutputer extends \Orgup\Application\Outputer {

    const DEBUG_TEMPLATE = 'main\elements\debuginner.htm';

    private $Data;
    private $Templator;

    public function __construct( \Orgup\Expansions\AjaxTemplator $Templator, \Orgup\DataModels\AjaxData $Data, Ways $Ways ) {

        $this->Data = $Data;
        $this->Templator = $Templator;
        $this->Ways = $Ways;
    }

	public function get_output() {

        $this->Data->postInit();

        $js                 = $this->Data->get_js();
        $notifications      = $this->Data->get_notifications();
        $errors             = $this->Data->get_errors();
        $cache_time         = $this->Data->get_cache_time();
        $scripts            = $this->Data->get_scripts();

        $scripts = $this->merge_keys( $this->required_scripts, $scripts );

		$xml = '<labels>';

		if ( $this->Data->get_templates() ) {
			foreach ( $this->Data->get_templates() as $template_key => $template ) {
				$xml .= '<html key="'.$template_key.'"><![CDATA['.$this->get_html( $template ).']]></html>';
			}
		}

		if ( !empty( $errors ) ) {
			foreach ( $errors as $err ) {
				$xml .= '<error><![CDATA['.$err.']]></error>';
			}
		}

		if ( !empty( $notifications ) ) {
			foreach ( $notifications as $mess ) {
				$xml .=  '<message><![CDATA['.$mess.']]></message>';
			}
		}

        // запускаем нужные функции после получения
		if ( !empty( $js ) ) {
			foreach ( $js as $i ) {
				$xml .= '<function><funcname>'.$i['function_name'].'</funcname>';
				if ( isset( $i['parameters'] ) ) {
					foreach ( $i['parameters'] as $k => $p ) {
                        if ( is_array( $p ) && isset( $p['translate_it'] ) )
                            $p = $this->translate_it( $p );
						$xml .= '<parameter><name>'.$k.'</name><value><![CDATA['.$p.']]></value></parameter>';
					}
				}
				$xml .= '</function>';
			}
		}

        // добавляем нужные файлы скриптов на страницу
        if ( !empty( $scripts ) ) {

            $scripts_paths = $this->init_scripts( $scripts );

            foreach ( $scripts_paths as $script ) {
                $xml .= '<script><![CDATA['.$script.']]></script>';
            }
        }

        if ( $cache_time !== 0 ) {
            $xml .= '<cachetime>'.$cache_time.'</cachetime>';
        }

        if ( $this->Data->getDebug() ) {
            $xml .= '<log><![CDATA['.$this->get_html( self::DEBUG_TEMPLATE ).']]></log>';
        }

		$xml .= '</labels>';

        $this->_send_headers();
        return $xml;
	}

    private function get_html( $block ) {

        $template = $this->Templator->get_template_engine()->loadTemplate( $block );
        return $template->render( array( 'data' => $this->Data, 'lang' => $this->Data->getLang(), 'ways' => $this->Ways ) );
    }

    private function translate_it( $p ) {

        $lang = $this->Data->getLang();
        return isset( $lang['ajax'][$p['type']][$p['param']] ) ?
             $lang['ajax'][$p['type']][$p['param']] : 'I was not able to translate title';
    }

    private function _send_headers() {
        header('Pragma: no-cache');
        header('Cache-Control: no-store');
        header('Content-Type: application/xml');
    }

    private function init_scripts( $scripts_keys ) {
        return $this->scripts = $this->get_array_from_file( $scripts_keys, ROOTDIR.self::SCRIPTS_VERSION, ROOTDIR.self::SCRIPTS );
    }
}