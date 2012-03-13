<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 20.09.11
 * Time: 13:08
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Application\ConfigLoader;
use \Orgup\Application\Logger;

class YMLLoader {

    private static $Loader;

    /**
     * @static
     * @return \Orgup\Application\ConfigLoader\Spyc;
     */
    public static function getLoader()
    {

        if(self::$Loader === null)
        {
            if(function_exists('yaml_parse_file'))
            {
                self::$Loader = new NativeYaml();
                Logger::log('Using native YAML');
            }
            else
            {
                self::$Loader = new Spyc();
                Logger::log('Using PHP YAML');
            }
        }


        return self::$Loader;
    }
}
