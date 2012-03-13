<?php

namespace Orgup\Application;

class Logger {

    static private $log = array();
    static private $start_time;

    static public function log( $message, $file = '', $line = 0, $dump = null )
    {
        self::addToLog($message, $file, $line, $dump,'log');
    }

    static public function err( $message, $file = '', $line = 0, $dump = null ) {

        self::addToLog($message, $file, $line, $dump,'error');
    }

    static public function good( $message, $file = '', $line = 0, $dump = null ) {

        self::addToLog($message, $file, $line, $dump,'good');
    }

    static private function addToLog( $message, $file, $line, $dump, $type)
    {
        self::set_time();

        if ( !is_string( $message ) )
            $message = serialize( $message );

        $memory = self::memoryUsage();

        $id = array_push(self::$log, array(
            'time' => microtime( TRUE ),
            'message' => $message,
            'line' => $line,
            'file' => $file,
            'type' => $type,
            'dump' => $dump,
            'memory' => $memory,
            'memory_usage' => $memory,
            'microtime' => round( microtime( TRUE ) - self::$start_time, 3 )
        ));

        if($id > 1)
        {
            self::$log[$id - 1]['memory_usage'] = round(self::$log[$id - 1]['memory'] - self::$log[$id - 2]['memory'], 3);
        }
    }
    
    public static function memoryUsage()
    {
        return round(memory_get_usage(true) / 1048576, 3);
    }

    static public function getLog() {

        $output_log = array();
        $queriesCount = 0;

        foreach ( self::$log as $key => $log ) {

            $output_log[$key] = $log;

            if( mb_substr( $log['message'], 0, 3 ) == 'SQL' ) {
                $output_log[$key]['type'] = 'sql';
                ++$queriesCount;
            }
        }

        return array(
            'log' => $output_log,
            'memory' => round( memory_get_peak_usage() / ( 1024 * 1024 ), 2 ),
            'queriesCount' => $queriesCount,
            'totalTime' => round(  microtime( true ) - self::$start_time, 3 )
        );
    }

    static private function set_time() {
        if( !self::$start_time )
            self::$start_time = microtime(TRUE);
    }
}
