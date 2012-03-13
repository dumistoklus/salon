<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 21.11.11
 * Time: 15:41
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Common\DBAccess;
use Orgup\Application\Registry;

class DBAccess
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private static $DB;

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public function getDB()
    {
        return DBAccess::sGetDB();
    }

    public static function sGetDB()
    {
        if(self::$DB == null)
        {
            self::$DB = Registry::instance()->get('db');
        }

        return self::$DB;
    }
}
