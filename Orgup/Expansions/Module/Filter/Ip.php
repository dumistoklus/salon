<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 03.11.11
 * Time: 14:55
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Expansions\Module\Filter;
use \Orgup\Application\Registry;
use \Orgup\Application\Exception\Module\E404;

class Ip extends  Filter
{
    public function isValid()
    {
        $valid = false;

        $userIp = Registry::instance()->User()->getIP();

        foreach($this->getParams() as $ip)
        {
            if($userIp == $ip) return true;
        }

        return $valid;
    }

    public function ifInvalid()
    {
        throw new E404();
    }
}
