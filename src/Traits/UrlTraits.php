<?php

namespace SustainableWeb\WebsiteCarbonCalculator\Traits;

use Sabre\Uri;

trait UrlTraits {

	/**
	 * @throws Uri\InvalidUriException
	 */
	private static function normaliseURL(string $url): string
    {
        if($url == '') {
            return '';
        }

        $url = self::addHttp($url);
        $url = Uri\normalize($url);

        $url = rtrim($url, '/');

        return $url;
    }

	private static function urlHost(string $url): string
    {
        $url_parts = parse_url($url);

        return self::stripWWW($url_parts['host']);
    }

	private static function urlHostAndPath(string $url): string
    {
        $url_parts = parse_url($url);

        $my_url = $url_parts['host'];

        if(!empty($url_parts['path'])) {
            $my_url .= $url_parts['path'];
        }

        return self::stripWWW($my_url);
    }

	private static function urlHostAndPathWithQuery(string $url): string
    {
        $url_parts = parse_url($url);

        $my_url = $url_parts['host'];

        if(!empty($url_parts['path'])) {
            $my_url .= $url_parts['path'];
        }

        if(!empty($url_parts['query'])) {
            $my_url .= '?' . $url_parts['query'];
        }

        return self::stripWWW($my_url);
    }

    private static function addHttp(string $url): string
    {
        if(!self::startsWith($url, 'http://') && !self::startsWith($url, 'https://')){
            $url = 'http://' . $url;
        }

        return $url;
    }

    private static function stripWWW(string $url): string
    {
        if(self::startsWith($url, 'www.')){
            $url = substr($url, 4);
        }

        return $url;
    }

    private static function startsWith(string $string, string $startString): bool
    {
        $len = strlen($startString);
        return (substr($string, 0, $len) === $startString);
    }
}
