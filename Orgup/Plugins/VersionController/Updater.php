<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 17.10.11
 * Time: 16:24
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Plugins\VersionController;

abstract class Updater 
{
    protected $directoriesWithChanges;
    protected $folder;
    protected $newConfig;
    protected $expansion = '';

    protected $unionFile;

    final function __construct(array &$directoriesWithChanges, &$folder, array &$newConfig)
    {
        $this->directoriesWithChanges = $directoriesWithChanges;
        $this->folder = $folder;
        $this->newConfig = $newConfig;

        $this->unionFile = 'union.'.$this->expansion;
    }


    public function rewriteUnionsInDirectory() {

        $filesToCompress = array();

        foreach($this->directoriesWithChanges as $directory)
        {
            $isUnionInDirectory = false;
            $compilerString = '';
            $dirArray = explode('/', strstr($directory, $this->folder));

            foreach ($this->newConfig as $name => $settings)
            {
                if(isset($settings['file']))
                {
                    //сравнивает путь к файлу и директорию
                    $configArray = explode('/', $settings['file']);
                    $fileName = array_pop($configArray);

                    if ($configArray == $dirArray)
                    {
                        if($fileName != $this->unionFile)
                        {
                            if( ! (isset($settings['not_compress']) && $settings['not_compress'] == 1) )
                            {
                                $compilerString .= file_get_contents(trim(ROOTDIR.'www'.$settings['file']));
                            }
                        }
                        else {
                            isset($settings['version']) ?: $settings['version'] = 0;
                            $this->newConfig[$name]['version'] = (int)$settings['version']+1;
                            $this->newConfig[$name]['lastupdate'] = time();
                            $isUnionInDirectory = true;
                        }
                    }
                }
            }

            if($isUnionInDirectory == false) {
                $newFile = array();
                $newFile['lastupdate'] = time();
                $newFile['version'] = 1;
                $newFile['file'] = strstr($directory, $this->folder).'/union.css';
                $name = 'union'.implode('_',$dirArray);
                $this->newConfig[$name] = $newFile;
            }

            $filesToCompress[] = array('file' => $directory.'/'.$this->unionFile, 'content' => $compilerString);
        }

        return $this->compress($filesToCompress);
    }

    protected abstract function compress(array $content);
}
