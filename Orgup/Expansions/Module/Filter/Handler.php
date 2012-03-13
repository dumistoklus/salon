<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 03.11.11
 * Time: 15:36
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Expansions\Module\Filter;
use \Orgup\Expansions\Module\ExpansionHandler;

class Handler implements ExpansionHandler
{
    private $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function run()
    {
        foreach ($this->filters as $filter)
        {
            $filterClass = $filter['module'];
            $Filter = new $filterClass($filter['param']);

            if( ! $Filter->isValid())
            {
                $Filter->ifInvalid();
            }
        }
    }
}
