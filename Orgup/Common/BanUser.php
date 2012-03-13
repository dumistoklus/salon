<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 29.11.11
 * Time: 15:48
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Common;
use \Orgup\Common\DBAccess\DBAccess;

class BanUser extends DBAccess
{
    public static function ban($userId)
    {
        $result = self::sGetDB()->update('users', array('active' => '0'), array('id_user' => $userId ));
        self::sGetDB()->delete('sessions', array('id_user' => $userId ));

        if($result > 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}
