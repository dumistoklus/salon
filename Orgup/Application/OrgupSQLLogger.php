<?php

namespace Orgup\Application;

use Doctrine\DBAL\Logging\SQLLogger;

class OrgupSQLLogger implements SQLLogger {

    public function __construct() {
        $this->enabled = Registry::instance()->get('debug');
    }

    /** @var array $queries Executed SQL queries. */
    public $queries = array();

    /** @var boolean $enabled If Debug Stack is enabled (log queries) or not. */
    public $enabled = true;

    public $start = null;

    public $currentQuery = 0;

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        if ($this->enabled) {
            $this->start = microtime(true);
            $this->queries[++$this->currentQuery] = array('sql' => $sql, 'params' => $params, 'types' => $types, 'executionMS' => 0);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
        if ($this->enabled) {
            $this->queries[$this->currentQuery]['executionMS'] = microtime(true) - $this->start;
            Logger::log( "SQL: ".$this->prepare( $this->queries[$this->currentQuery]['sql'],  $this->queries[$this->currentQuery]['params'] ) );
        }
    }

    private function prepare( $sql, $params ) {

        if ( !is_array( $params ) || empty( $params ) ) {
            return $sql;
        }

        foreach ( $params as $key => $value ) {
            if ( is_string( $value ) ) {
                $params[$key] = "'".$value."'";
            }
        }

        foreach ( $params as $key => $value ) {

            if ( isset( $params[0] ) ) {
                $key = '?';
                $sql = $this->replace( $sql, $key, $value );
            } else {
                if ( mb_substr( $key, 0, 1 ) != ':') {
                    $key = ':'.$key;
                }

                for( $i = 0; $i <= mb_substr_count( $sql, $key ); $i++ ) {
                    $sql = $this->replace( $sql, $key, $value );
                }
            }
        }

        return $sql;
    }

    private function replace( $sql, $key, $value ) {

        $position = mb_strpos( $sql, $key );
        if ( !$position ) {
            Logger::err( "Maybe wrong SQL-query:".$sql );
        }
        $begin = mb_substr( $sql, 0, $position );
        $end =  mb_substr( $sql, $position + mb_strlen( $key ) );
        $sql = $begin .$value.$end;

        return $sql;
    }
}