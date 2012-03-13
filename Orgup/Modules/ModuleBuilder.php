<?php

namespace Orgup\Modules;

use Orgup\Application\Registry;
use Orgup\Application\Logger;
use \Orgup\Application\Exception\HTTP\ParamException;

abstract class ModuleBuilder {

    protected $Data;
    /**
     * @var \Orgup\Application\Routing
     */
    protected $Routing;
    protected $Ways;

    private $way_pattern;

    public function __construct(
        \Orgup\DataModels\Data $Data,
        \Orgup\Application\Routing $Routing,
        \Orgup\Expansions\Ways $Ways
    ) {
        $this->Data = $Data;
        $this->Routing = $Routing;
        $this->Ways = $Ways;

        Logger::log( 'Construct module', __FILE__, __LINE__ );
    }

    public function actionsBeforeRun() {}

    public function run() {}

    public function actionsAfterRun() {}

    /**
     * @return \Doctrine\DBAL\Connection
     */
    protected function getDB()
    {
        return Registry::instance()->get('db');
    }
    /**
     * @return \Orgup\Expansions\Ways
     */
    public function getWays()
    {
        return $this->Ways;
    }

    public function getClassName()
    {
        return get_class($this);
    }

    public function setWayPattern( $way_pattern ) {
        $this->way_pattern = $way_pattern;
    }


    public function getWayPattern($num = null)
    {
        if($num !== null)
        {
            if(isset($this->way_pattern[$num]))
                return $this->way_pattern[$num];
            else
            {
                throw new ParamException('num '.$num .' doesn\'t exists');
            }
        }

        return $this->way_pattern;
    }
}