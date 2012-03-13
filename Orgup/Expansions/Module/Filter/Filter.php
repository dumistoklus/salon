<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 03.11.11
 * Time: 14:55
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Expansions\Module\Filter;

abstract class Filter
{
    private $params;

    protected final function getParams()
    {
        return $this->params;
    }

    public function __construct($filterParams)
    {
        if(is_array($filterParams))
        {
            $this->params = $filterParams;
        }
        else
        {
            $this->params[0] = $filterParams;
        }
    }

    abstract public function ifInvalid();
    abstract public function isValid();
}