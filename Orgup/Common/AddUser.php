<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 12.11.11
 * Time: 15:47
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Common;
use \Orgup\Application\Registry;

class AddUser
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private static $DB;

    public static function add($data = array())
    {
        if (self::$DB == null)
        {
            self::$DB = Registry::instance()->get('db');
        }

        if(!isset($data['created']))
        {
            $data['created'] = time();
        }

        self::$DB->insert('users', $data);

        return new User(self::$DB->lastInsertId());
    }
}
