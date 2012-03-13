<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 03.11.11
 * Time: 15:37
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Expansions\Module;

interface ExpansionHandler {
    public function __construct(array $expansions);
    public function run();
}
