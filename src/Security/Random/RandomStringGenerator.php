<?php
/**
 * Created by PhpStorm.
 * User: czar
 * Date: 08/09/2018
 * Time: 5:36 PM
 */

namespace App\Security\Random;


class RandomStringGenerator
{
    public function generate($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
    {
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; $i ++) {
            $pieces[] = $keyspace[random_int(0, $max)];
        }

        return implode('', $pieces);
    }
}