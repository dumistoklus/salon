<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 20.09.11
 * Time: 12:50
 * To change this template use File | Settings | File Templates.
 */
//yaml_parse_file

namespace Orgup\Application\ConfigLoader;
use \Orgup\Application\Logger;
use \Orgup\Cache\Cache;
use \Orgup\Application\ConfigIsWrong;

class NativeYaml {
    public function loadFile($file)
    {
        if(file_exists($file))
        {
            Logger::log('Load from cache file '.$file);

            $Cache = Cache::instance();

            $fileEditTime = filemtime($file);
            Logger::good("File `$file` edit time ".date('F d Y H:i:s', $fileEditTime));
            $md5 = md5($fileEditTime.$file);
            $parsed = $Cache->findYml($md5);

            if(!$parsed)
            {
                Logger::err('File '.$file . ' not found in cache');
                Logger::log('YAML '.$file);

                $parsed = yaml_parse_file($file);

                $Cache->insertYml($md5, $parsed, $file);

                Logger::log('END YAML');
            }
            else Logger::log('File '.$file.' from cache');

            Logger::log('END');

            return $parsed;
        }

        return array();
    }

    public function YAMLDump($data)
    {
        return yaml_emit($data);
    }
}
