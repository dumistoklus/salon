<?php

namespace Orgup\DataModels;
use Orgup\Application\Registry;
use \Orgup\Common\Localization;

interface UserErrorsAndNotifications {

    public function add_error( $error_text, $module_name );
    public function get_errors();
    public function add_notification( $notification_text, $module_name );
    public function get_notifications();
}

class ErrorNotStringAndNotArray extends \Exception {}
class NotificationNotString extends \Exception {}

interface DataInterface {

    public function imember();
    public function user();
    public function set_locale( Localization $locale );
    public function get_log();
    public function time();
    public function getLang();
    public function getDebug();
    public function setDebug( $debug );
    public function Routing();
}

abstract class Data implements UserErrorsAndNotifications, DataInterface {

    protected $errors = array();
    protected $notifications = array();
    protected $lang = array();
    protected $debug = false;

    private $Module;

    protected $city_id;
    protected $city_id_checked;

    function __construct()
    {
        $this->init_styles();
    }

    public function setModule($Module)
    {
        $this->Module = $Module;
    }
    /**
     * @return \Orgup\Modules\ModuleBuilder
     */
    public function getModule()
    {
        return $this->Module;
    }

    public function closeModule()
    {
        \Orgup\Application\Logger::log('Unset Module..');
        unset($this->Module);
        gc_collect_cycles();
        \Orgup\Application\Logger::log('END unset');
    }

    /**
     * @return \Orgup\Application\Routing
     */
    public function Routing()
    {
        return Registry::instance()->get('Routing');
    }

	protected function init_styles() { }
    public function initStylesAndScripts() {}

    /**
     * @throws ErrorNotStringAndNotArray
     * @param $error_text
     * @param $module_name
     * @return bool
     */
    public function add_error( $error_text, $module_name ) {

        if ( is_string( $error_text ) ) {
            $this->errors[] = array( 'text' => $error_text, 'module_name' => $module_name );
            return true;
        }

        if ( is_array( $error_text ) ) {
            foreach ( $error_text as $error ) {
                $this->errors[] = array( 'text' => $error, 'module_name' => $module_name );
            }

            return true;
        }

        throw new ErrorNotStringAndNotArray();
    }

    /**
     * @param $error
     * @return void
     */
    public function add_custom_error($error)
    {
        $this->errors[] = array( 'text' => $error, 'module_name' => '' );
    }

    /**
     * @return array
     */
    public function get_errors() {
        return $this->translator( $this->errors, 'errors' );
    }

    /**
     * @throws NotificationNotString
     * @param $notification_text
     * @param $module_name
     * @return array
     */
    public function add_notification( $notification_text, $module_name ) {
        if ( is_string( $notification_text ) )
            return $this->notifications[] = array( 'text' => $notification_text, 'module_name' => $module_name );
        throw new NotificationNotString('Notification is strongly text');
    }

    /**
     * @return array
     */
    public function get_notifications() {
        return $this->translator( $this->notifications, 'notifications' );
    }

    /**
     * @return bool
     */
    public function imember() {
        return Registry::instance()->User()->imember();
    }

    /**
     * @return object \Orgup\Common\User
     */
    public function user() {
        return Registry::instance()->User();
    }

    /**
     * @param array $locale
     * @return void
     */
    public function set_locale( Localization $locale ) {
        $this->lang = $locale;
    }

    /**
     * @return array
     */
    public function get_log() {
        if ( $this->debug )
            return \Orgup\Application\Logger::getLog();
    }

    /**
     * @return int
     */
    public function time() {
        return time();
    }

    /**
     * @return array
     */
    public function getLang() {
        return $this->lang;
    }

    /**
     * @return bool
     */
    public function getDebug() {
        return $this->debug;
    }

    /**
     * @param $debug
     * @return void
     */
    public function setDebug( $debug ) {
        $this->debug = (bool)$debug;
    }

    protected function translator( $array, $type ) {

        $output = array();

        foreach ( $array as $message ) {
            if ( isset( $this->lang[$message['module_name']][$type][$message['text']] ) ) {
                $output[] = $this->lang[$message['module_name']][$type][$message['text']];
            } else {
                $output[] = $message['text'];
                \Orgup\Application\Logger::err( 'Wrong param for translate:'.$message['text'].' in module '.$message['module_name'], __FILE__, __LINE__ );
            }
        }

        return $output;
    }

    /**
     * делает число красивым
     * @param $sum
     * @return string
     */
    public function beautiful_sum( $sum ) {

        $sum = (string)$sum;

        $length = mb_strlen( $sum );

        $result = '';

        for ( $i = $length; $i >= 0; $i-- ) {
            $result = mb_strcut( $sum, $i, 1 ).$result;
            if ( ( $i - $length ) % 3 == 0 && $length != $i && $i != 0 ) {
                $result = '&nbsp;'.$result;
            }
        }

        return $result;
    }

    public function has_errors() {
        return count( $this->errors ) > 0;
    }
}