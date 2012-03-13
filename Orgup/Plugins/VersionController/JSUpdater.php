<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ll
 * Date: 17.10.11
 * Time: 11:12
 * To change this template use File | Settings | File Templates.
 */
namespace Orgup\Plugins\VersionController;

class JSUpdater extends Updater {

    protected $expansion = 'js';

    protected function compress(array $data)
    {

        $cmh = curl_multi_init();

        $tasks = array();

        foreach ($data as $value)
        {

            $ch = curl_init('http://closure-compiler.appspot.com/compile');


            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            curl_setopt($ch, CURLOPT_HEADER, 0);

            curl_setopt($ch, CURLOPT_POST, 1 );

            $post_data = 'compilation_level=SIMPLE_OPTIMIZATIONS'
                         .'&output_format=text'
                         .'&output_info=compiled_code'
                         .'&js_code='.urlencode($value['content']);

            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

            $tasks[$value['file']] = $ch;

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

                        $file = array_search($ch, $tasks);

                        unset($tasks[$file]);

                        $content = curl_multi_getcontent($ch);

                        curl_multi_remove_handle($cmh, $ch);
                        curl_close($ch);

                        $union = fopen(trim($file), 'w');
                        ftruncate($union, 0);
                        fwrite($union, $content);
                        fclose($union);
                    }
                }
                while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }

        curl_multi_close($cmh);

        return true;
    }
}
