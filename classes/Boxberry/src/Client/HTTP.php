<?php
/**
 *
 *  * This file is part of Boxberry Api.
 *  *
 *  * (c) 2016, T. I. R. Ltd.
 *  * Evgeniy Mosunov, Alexander Borovikov
 *  *
 *  * For the full copyright and license information, please view LICENSE
 *  * file that was distributed with this source code
 *  *
 *  * File: HTTP.php
 *  * Created: 26.07.2016
 *  *
 */

namespace Boxberry\Client;

use Boxberry\Client\Exceptions\BadResponseException;

/**
 * Class HTTP
 * @package Boxberry\Client
 */
class HTTP
{
    public static $lifetime = 3600;


    public static function setCacheTimeLife($time)
    {
        self::$lifetime = $time;
    }

    public static function get_cache($key)
    {
        if ($cache = get_transient($key)) {
            return $cache;
        }

        delete_transient($key);

        return false;
    }

    public static function set_cache($key, $cnt)
    {
        return set_transient($key, $cnt, self::$lifetime);
    }

    /**
     * @param $api_url
     * @param $args
     * @return Response
     * @throws BadResponseException
     */
    public static function get($api_url, $args)
    {
        return self::send('GET', $api_url, $args);
    }

    /**
     * @param $api_url
     * @param $args
     * @return Response
     * @throws BadResponseException
     */
    public static function post($api_url, $args)
    {
        return self::send('POST', $api_url, $args);
    }

    /**
     * @param $method
     * @param $api_url
     * @param $args
     * @return Response
     * @throws BadResponseException
     */
    private static function send($method, $api_url, $args)
    {
        $url = add_query_arg($args, $api_url);
        $hashRequest = 'bxb_'.md5($url);

        if ($out = self::get_cache($hashRequest)) {
            $answer = json_decode($out, true);
        } else {
            if ($method === 'GET') {
                $result = wp_remote_get($url);
            } else {
                $result = wp_remote_post($api_url, $args);
            }

            $out = wp_remote_retrieve_body($result);

            if (wp_remote_retrieve_response_code($result) !== 200) {
                throw new BadResponseException();
            }

            if (!$answer = json_decode($out, true)) {
                throw new BadResponseException();
            }

            if (!empty($answer['err'])) {
                throw new BadResponseException($answer['err']);
            }

            if (isset($answer[0]['err'])) {
                throw new BadResponseException($answer[0]['err']);
            }

            if ($method === 'GET'){
                self::set_cache($hashRequest, $out);
            }

        }

        return new Response($answer);
    }
}