<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 13.09.11
 * Time: 14:59
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Plugins\VersionController;
require_once(ROOTDIR.'system/CssTidy/class.csstidy.php');

use \Orgup\Application\ConfigLoader\YMLLoader;

class VersionControllerUpdater {

    private $expansion;
    private $directories = array();
	private $config = array();
    private $orgupConfig = array();
	private $configPath;
    private $orgupConfigPath;
    private $generalConfig = array();
	private $errors = array();
	private $newConfig = array();
    private $isActual = true;
    private $convertedPath;
    private $filesToWrite = array();
	private $spyc;
    private $www;
    private $timeVersion;

    public function __construct($expansion, $config_path, $orgup_config_path, $converted_path) {

        $this->www = ROOTDIR.'www';
        $this->convertedPath = $converted_path;
	    $this->expansion = $expansion;
	    $this->configPath = $config_path;
        $this->orgupConfigPath = $orgup_config_path;
	    $this->spyc = YMLLoader::getLoader();
        $this->config = $this->spyc->loadFile( $this->configPath);
        $this->orgupConfig = $this->spyc->loadFile( $this->orgupConfigPath);
        $this->timeVersion = time() - 1322640000;
    }

	public function run() {

        if(!$this->scan())
            return;
        if(!$this->setOutputInFilesToWrite())
            return;
        if(!$this->setNewContent())
            return;
        if(!$this->setUnionsNewContent())
            return;
        if(!$this->setOldContent())
            return;
        if(!$this->createCovertedDirectory())
            return;
        if(!$this->writeChangeFile())
            return;
        if(!$this->createCleanConverted())
            return;
        if(!$this->writeNewConfig())
            return;
        if(!$this->writeInConverted())
            return;

        return true;
	}

    private function scan() {
        foreach ($this->config as $name=>$info) {

            if(empty($info['file'])) {
                $this->errors[] = 'config_without_filepath';
                return;
            }

            if($this->isHTTPFile($info['file']) || isset($info['exclude'])) {

                $this->generalConfig[$name]['file'] = $info['file'];
                $this->generalConfig[$name]['exclude'] = 1;
                
            } else {

                $file_path = $this->www.$info['file'];

                if(!is_file($file_path)) {
                    $this->errors[] = 'empty_file';
                    return;
                }

                $directory = $this->returnDirectoryByPath($info['file']);
                $this->directories[$directory] = 1;
                $this->generalConfig[$name]['directory'] = $directory;

                if(isset($info['not_include'])) {
                    $this->generalConfig[$name]['file_name'] = $this->returnNameByPath($directory).'_'.basename($file_path, '.'.$this->expansion);
                    $this->generalConfig[$name]['file'] = isset($this->orgupConfig[$name]['file']) ? $this->orgupConfig[$name]['file'] : '';
                } else {
                    $this->generalConfig[$name]['union'] = $directory;
                }

                $content  = file_get_contents($file_path);
                if(isset($info['not_include']) && isset($info['not_compress'])) {
                    $this->filesToWrite[$name]['not_compress_not_include'] = $content;
                } else if (isset($info['not_include'])) {
                    $this->filesToWrite[$name]['compress'] = $content;
                } else if (isset($info['not_compress'])) {
                    $this->filesToWrite[$directory]['not_compress'] = isset($this->filesToWrite[$directory]['not_compress']) ? $this->filesToWrite[$directory]['not_compress'].$content : $content;
                } else {
                    $this->filesToWrite[$directory]['compress'] = isset($this->filesToWrite[$directory]['compress']) ? $this->filesToWrite[$directory]['compress'].$content : $content;
                }

            }
        };
        return true;
    }

    private function writeInConverted() {
        foreach ($this->generalConfig as $name=>$info) {
            if(isset($info['exclude']))
                continue;

            if(empty($info['union'])) {
                if(!$this->fileWrite($this->www.$this->generalConfig[$name]['file'], $info['new_content']))
                    return;
            }
        }
        return true;
    }

    private function writeChangeFile() {
        foreach ($this->generalConfig as $name=>$info) {
            if(isset($info['exclude']))
                continue;

            if(empty($info['union'])) {
                if($info['new_content'] != $info['old_content']) {
                    $this->isActual = false;
                    $this->generalConfig[$name]['file'] = $this->convertedPath.'/'.$info['file_name'].'.'.$this->timeVersion.'.'.$this->expansion;
                }
            }
        }
        return true;
    }


    private function setOutputInFilesToWrite() {
         if($this->expansion == 'css') {
            if(!$this->cssSetOutputInFilesToWrite())
                return;
        }

        if($this->expansion == 'js') {
            if(!$this->jsSetOutputInFilesToWrite())
                return;
        }
        return true;
    }

    private function setOldContent() {
        foreach ($this->generalConfig as $name=>$info) {
            if(isset($info['exclude']))
                continue;

            if(empty($info['union'])) {
                $this->generalConfig[$name]['old_content'] = (is_file($this->www.$info['file'])) ? file_get_contents($this->www.$info['file']) : '';
            }
        }
        return true;
    }

    private function setUnionsNewContent() {
        foreach ($this->directories as $dir=>$i) {
            if(empty($this->filesToWrite[$dir]))
                continue;
            $union_name = $this->returnNameByPath($dir).'_union';
            $this->generalConfig[$dir]['file_name'] = $union_name;
            $this->generalConfig[$dir]['file'] = isset($this->orgupConfig[$dir]['file']) ? $this->orgupConfig[$dir]['file'] : '';
            $this->generalConfig[$dir]['new_content'] = $this->filesToWrite[$dir]['output'];
            $this->generalConfig[$dir]['is_union'] = 1;
        }
        return true;
    }

    private function createCovertedDirectory() {
        if(!is_dir($this->www.$this->convertedPath)) {
            mkdir($this->www.$this->convertedPath);
        }
        return true;
    }

    private function writeNewConfig() {
        $this->newConfig['last_update'] = time();
        foreach($this->generalConfig as $name=>$info) {
            if (isset($this->generalConfig[$name]['union']))
                $this->newConfig[$name]['file'] = $this->generalConfig[$this->generalConfig[$name]['union']]['file'];
            else
                $this->newConfig[$name]['file'] = $this->generalConfig[$name]['file'];
        }

        if (!$this->fileWrite($this->orgupConfigPath, $this->spyc->YAMLDump($this->newConfig)))
			return;
        return true;
    }

    private function createCleanConverted() {
        $converted_path = $this->www.$this->convertedPath;

        if (is_dir($converted_path)) {
            $this->removeDir($converted_path);
        } else {
            mkdir($converted_path);
        }
        return true;
    }

    private function removeDir($dir){
        if ($objs = glob($dir."/*")) {
            foreach($objs as $obj) {
                is_dir($obj) ? $this->removeDir($obj) : unlink($obj);
            }
        }
        return true;
    }

    private function setNewContent() {
        foreach ($this->generalConfig as $name=>$info) {
            if(empty($info['union']) && empty($info['exclude'])) {
                $this->generalConfig[$name]['new_content'] = $this->filesToWrite[$name]['output'];
            }
        }
        return true;
    }

    private function returnNameByPath($path) {
        $arrayPath = explode('/', $path);
        array_shift($arrayPath);
        return implode('_', $arrayPath);
    }

    public function isActual() {
        return $this->isActual;
    }

    public function lastUpdateConfig() {
        return isset($this->orgupConfig['last_update']) ? $this->orgupConfig['last_update'] : '0';
    }

    public function isOk() {
        return (count($this->errors) == 0);
    }

	public function getErrors() {
		return $this->errors;
	}

    private function cssSetOutputInFilesToWrite() {

        foreach ($this->filesToWrite as $name=>$content) {
            $output = '';

            if (isset($content['not_compress'])) {
               $output .= $content['not_compress'];
            }
            if (isset($content['compress'])) {
                $output .= $this->cssCompressString($content['compress']);
            }
            if(isset($content['not_compress_not_include'])) {
                $output .= $content['not_compress_not_include'];
            }
            
            $this->filesToWrite[$name]['output'] = $output;
        }
        return true;
    }

    private function jsSetOutputInFilesToWrite() {

        $cmh = curl_multi_init();

        $tasks = array();

        foreach ($this->filesToWrite as $name=>$content)
        {
            if (isset($content['not_compress_not_include'])) {
                $this->filesToWrite[$name]['output'] = $content['not_compress_not_include'];
            }
            if(isset($content['not_compress'])) {
                $this->filesToWrite[$name]['output'] = $content['not_compress'];
            }
            if(isset($content['compress'])) {
                $compressString = $content['compress'];
            } else
                continue;


            $ch = curl_init('http://closure-compiler.appspot.com/compile');

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            curl_setopt($ch, CURLOPT_HEADER, 0);

            curl_setopt($ch, CURLOPT_POST, 1 );

            $post_data = 'compilation_level=SIMPLE_OPTIMIZATIONS'
                         .'&output_format=text'
                         .'&output_info=compiled_code'
                         .'&js_code='.urlencode($compressString);

            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

            $tasks[$name] = $ch;

            curl_multi_add_handle($cmh, $ch);
        }

        $active = null;

        do {
            $mrc = curl_multi_exec($cmh, $active);
        }
        while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active && ($mrc == CURLM_OK)) {

            if (curl_multi_select($cmh) != -1) {

                do {
                    $mrc = curl_multi_exec($cmh, $active);

                    $info = curl_multi_info_read($cmh);

                    if ($info['msg'] == CURLMSG_DONE) {
                        $ch = $info['handle'];

                        $name = array_search($ch, $tasks);

                        unset($tasks[$name]);

                        $output = curl_multi_getcontent($ch);

                        curl_multi_remove_handle($cmh, $ch);
                        curl_close($ch);

                        $this->filesToWrite[$name]['output'] = isset($this->filesToWrite[$name]['output']) ? $this->filesToWrite[$name]['output'].$output : $output;
                    }
                }
                while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }

        curl_multi_close($cmh);

        return true;
    }

    private function changeUnionLastUpdate() {

        foreach ($this->directories as $directory=>$info) {
            if(empty($info['is_changes']))
            continue;

            $this->generalConfig[$this->directories[$directory]['union']]['last_update'] = filemtime($this->www.$this->generalConfig[$this->directories[$directory]['union']]['file']);
        }
    }

    private function formatDirectoriesByCompressedInclude() {

        foreach($this->directories as $directory=>$info) {

            if(empty($info['is_changes']))
            continue;

            $this->directories[$directory]['not_compress'] = '';
            $this->directories[$directory]['not_compress_not_include'] = array();
            $this->directories[$directory]['not_include'] = array();
            $this->directories[$directory]['compress_include'] = '';

            foreach($this->directories[$directory]['general_names'] as $general_name) {

                $content = file_get_contents($this->generalConfig[$general_name]['file_path']);

                if ($this->generalConfig[$general_name]['not_compress'] && $this->generalConfig[$general_name]['not_include']) {
                    $this->directories[$directory]['not_compress_not_include'][$general_name] = $content;

                } else if ($this->generalConfig[$general_name]['not_include']) {
                    $this->directories[$directory]['not_include'][$general_name] = $content;

                } else if ($this->generalConfig[$general_name]['not_compress']) {
                    $this->directories[$directory]['not_compress'] .= $content;

                } else {
                    $this->directories[$directory]['compress_include'] .= $content;
                }
            }
        }
        return true;
    }

    private function fileWrite($file_path, $content) {

        $f = fopen ($file_path, "w");
            if(!$f) {
                $this->errors[] = 'file_not_opened';
                return;
            }
            $isWrite = fwrite($f,$content);
		fclose($f);

        if(!$isWrite) {
            $this->errors[] = 'file_not_written';
            return;
        }
        return true;
    }

    private function cssCompressString($not_compress_string) {
        $css = new \csstidy();
        $css->set_cfg('remove_last_;',TRUE);
        $css->parse($not_compress_string);
        return preg_replace('/&#039;/','',$css->print->plain());
    }

    private function isHTTPFile($path) {
        return preg_match('/^http/',$path);
    }

    private function returnDirectoryByPath($path) {
        $pathArray = explode('/', $path);
        array_pop($pathArray);
        return implode('/', $pathArray);
    }
}