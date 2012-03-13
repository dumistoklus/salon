<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 19.11.11
 * Time: 18:43
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Expansions\Module\Filter;
use \Orgup\Application\Redirect;
use \Orgup\Application\Registry;

class Registered extends Filter
{

    public function ifInvalid()
    {
        $param = $this->getParams();
        $redirect = '/';

        if(isset($param['redirect'])) $redirect = $param['redirect'];

        throw new Redirect($redirect);
    }

    public function isValid()
    {
        $param = $this->getParams();

        if($param['allow'] == false && Registry::instance()->User()->imember()) return false;
        if($param['allow'] == true && !Registry::instance()->User()->imember()) return false;

        return true;
    }
}
