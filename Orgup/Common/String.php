<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 25.11.11
 * Time: 15:15
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Common;

class String
{
    private $string = '';

    function __construct($string)
    {
        $this->string = $string;
    }

    public function append($string)
    {
        $this->string .= $string;
    }

    function __toString()
    {
        return $this->string;
    }
}
