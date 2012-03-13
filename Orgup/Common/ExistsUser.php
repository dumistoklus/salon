<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 12.11.11
 * Time: 15:33
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Common;
use \Orgup\Application\Registry;
use \Orgup\Common\User;

class ExistsUser
{
    private $userName;
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $DB;
    private $userInfo;

    function __construct($username, $type = 'username', $additional = array())
    {
        $this->userName = $username;
        $this->DB = Registry::instance()->get('db');
        $this->userInfo = $this->db()->fetchAssoc('SELECT * FROM `users` WHERE `'.$type.'` = ? LIMIT 1', array($this->userName));
    }

    private function db()
    {
        return $this->DB;
    }

    public function exists()
    {
        return !empty($this->userInfo);
    }

    public function getUser()
    {
        return new User($this->userInfo['id_user']);
    }

    public function equals($type, $value)
    {
        return (isset($this->userInfo[$type]) && $this->userInfo[$type] == $value);
    }
}
