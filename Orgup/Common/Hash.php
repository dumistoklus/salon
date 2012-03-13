<?php

namespace Orgup\Common;

class Hash {
    static public function password( $password ) {
        for ( $i = 0; $i < 100; $i++ ) {
            $password = md5( $password );
        }

        return $password;
    }

    static public function genPassword($pass_length = 8)
    {
        $symbols = 'QqWwEeRrTtYyUuIiOoPpAaSsDdFfGgHhJjKkLlZzXxCcVvBbNnMm1234567890';
        $length = strlen($symbols) - 1;
        $password = '';

        for($i = 0; $i < $pass_length; $i++)
        {
            $letter = rand(0, $length);
            $password .= $symbols[$letter];
        }

        return $password;
    }
}