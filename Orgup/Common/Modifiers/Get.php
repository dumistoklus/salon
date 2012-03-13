<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 27.09.11
 * Time: 15:32
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Common\Modifiers;
use Orgup\Application\Registry;

class Get extends Post
{
    protected $errorType = 'Get';

    protected function routeParam($name)
    {
        return Registry::instance()->get('Routing')->route_get($name);
    }
}
