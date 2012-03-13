<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 17.10.11
 * Time: 11:04
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Plugins\VersionController;
require_once(ROOTDIR.'system/CssTidy/class.csstidy.php');

class CSSUpdater extends Updater
{
    protected $expansion = 'css';

    protected function compress(array $content) {


        $css = new \csstidy();
        $css->set_cfg('remove_last_;',TRUE);

        foreach($content as $value)
        {
            $css->parse($value['content']);
            $union = fopen($value['file'], 'w');
            ftruncate($union, 0);
            fwrite($union, preg_replace('/&#039;/','',$css->print->formatted()));
            fclose($union);
        }

        return true;
    }
}
