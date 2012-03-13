<?php

namespace Orgup\Common;
use Orgup\Application\Registry;
use Orgup\Application\Authentication;
use Orgup\Application\Exception\UserErrors;

class User {

    private $userData;
    private $changedUserData;
    private $data_is_obtaned = false;
    private $exceptions_fields = array( 'id_user' );
    private $Authentication;
    private $id_user;
    private $userRights;
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $DB;
    private $uniqueId;
    private $uniqueIdDbId;
    private $uid_checked = false;
    private $locale;
    private $defaultLocale;
    /**
     * @var \Orgup\Application\Routing
     */
    private $Routing;

    public function __construct( $id_user = 0 ) {

        $this->DB = Registry::instance()->get('db');
        $this->Routing = Registry::instance()->get('Routing');

        $this->defaultLocale = Registry::instance()->get('default_localization');

        $this->defineLocale();

        $this->Authentication = new Authentication( $this->DB , $this->Routing );

        $this->id_user = (int)$id_user;

        $this->uniqueId = $this->Routing->route_cookie('uniqueId');
    }

    private function initUID()
    {
        if(!$this->uid_checked)
        {
            if($this->uniqueId == null || !ctype_xdigit($this->uniqueId))
            {
                $this->createNewUid();
            }
            else
            {
                $this->checkUid();
            }

            $this->uid_checked = true;
        }
    }

    private function createNewUid()
    {
        $this->uniqueId = md5(microtime(false).$this->getUserAgent().$this->getIP());
        $this->Routing->setcookie('uniqueId', $this->uniqueId, time() + 60 * 60 * 24 * 720);
        $this->DB->insert('user_unique_id', array('uid' => $this->uniqueId, 'hua' => $this->getUserAgent(), 'city_id' => $this->getCityId()));

        return $this->uniqueIdDbId = $this->DB->lastInsertId();
    }

    private function checkUid()
    {
        $stmt = $this->DB->prepare('SELECT id, city_id FROM user_unique_id WHERE uid=:uid LIMIT 1');
        $stmt->bindValue('uid', $this->uniqueId, \PDO::PARAM_STR);

        $stmt->execute();

        $uid = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if(empty($uid) || $uid[0]['city_id'] != $this->getCityId())
        {
            $this->createNewUid();
        }
        else
        {
            $this->uniqueIdDbId = $uid[0]['id'];
        }
    }

    private function defineLocale()
    {
        $locale = $this->Routing->route_cookie('lang');

        if($locale)
        {
            $this->locale = $locale;
        }
        else
        {
            $this->locale = $this->getDefaultLocale();
        }
    }

    public function get( $key ) {

        $this->get_user_info_from_bd();

        if ( array_key_exists( $key, $this->userData ) )
            return $this->userData[$key];

        throw new UserErrors\UserPropertyNotFound();
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function getDefaultLocale()
    {
        return $this->defaultLocale;
    }

    public function getId()
    {
        return $this->get('id_user');
    }

    public function getUserAgent()
    {
        $hua = $this->Routing->route_server('HTTP_USER_AGENT');

        if ($hua == '')
        {
            $hua = 'NO_BROWSER';
        }

        return $hua;
    }

    public function getIP()
    {
        return $this->Routing->route_server('REMOTE_ADDR');
    }

    public function getUniqueID()
    {
        $this->initUID();
        return $this->uniqueId;
    }

    public function getUniqueDBID()
    {
        $this->initUID();
        return $this->uniqueIdDbId;
    }

    public function imember() {
        if( $this->get_user_info_from_bd())
            return !empty( $this->userData );

        return false;
    }

    /**
     * @param $right_name
     * @return bool
     */
    public function check_this_right( $right_name ) {

        if ( !$this->imember() )
            return false;

        if ( $this->userRights === null ) {
            $this->userRights = $this->get_rights_from_bd();
        }

        return in_array( $right_name, (array) $this->userRights );
    }

    public function set( $key, $value, $saveAfterSet = FALSE ) {

        $this->get_user_info_from_bd();

        if ( $this->data_is_obtaned AND $this->userData === null )
            throw new UserErrors\UserDataNotReceived;

        if ( !array_key_exists( $key, $this->userData ) )
            throw new UserErrors\UserPropertyNotFound();

        if ( in_array( $key, $this->exceptions_fields ) )
            throw new UserErrors\ThisUserPropertyCanNotBeChanged();

        $this->userData[$key] = $this->changedUserData[$key] = $value;


        if ( $saveAfterSet )
            return $this->saveData();

        return true;
    }

    public function saveData() {

        if ( !$this->data_is_obtaned )
            return false;

        $this->finish_script();
        
        $result = $this->DB->update('users', $this->changedUserData, array('id_user' => $this->id_user ));

        if($result)
        {
            $this->changedUserData = array();
        }

        return (bool)$result;
    }

    /**
     * @return int
     */
    public function getCityId()
    {
        return  Registry::instance()->get('geo')->getCityId();
    }

    public function getMainCityId()
    {
        return $this->get('city_id');
    }

    private function get_id_user_from_auth() {

        if ( $id_user = $this->Authentication->get_user_id() ) {
            $this->id_user = $id_user;
            return true;
        }

        return false;
    }

    private function get_user_info_from_bd() {

        if ( $this->data_is_obtaned )
            return true;

        if ( !$this->id_user )
            $this->get_id_user_from_auth();

        if ( !$this->id_user )
            return false;

        $userData = $this->DB->fetchAssoc(
           "SELECT * FROM users WHERE id_user = :id_user AND `active` = '1'",
            array( ':id_user'=> $this->id_user ),
            array( \PDO::PARAM_INT )
        );

        $this->data_is_obtaned = true;

        if ( $userData ) {
            $this->userData = $userData;
            return true;
        }

        return false;
    }

    private function get_rights_from_bd() {
        $rights_from_bd = $this->DB->fetchAll('SELECT `right` FROM `users_rights` WHERE `id_user` = ?', array( $this->id_user ), 0 );
        if ( !empty( $rights_from_bd ) ) {

            $rights_array = array();

            for ( $i = 0; $i < count( $rights_from_bd ); $i++ ) {
                $rights_array[] = $rights_from_bd[$i]['right'];
            }

            return $rights_array;
        }

        return array();
    }

    private function finish_script() {
        $this->set( 'last_visit', time() );
    }
}