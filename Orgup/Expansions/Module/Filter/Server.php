<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 12.11.11
 * Time: 12:30
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Expansions\Module\Filter;
use \Orgup\Application\Exception\Module\E404;
use \Orgup\Application\Registry;

class Server extends Filter
{
    public function isValid()
    {
        foreach($this->getParams() as $type => $needed)
        {
            $value = Registry::instance()->get('Routing')->route_server($type);
            if($value != $needed) return false;
        }

        return true;
    }

    public function ifInvalid()
    {
        throw new E404();
    }

}
