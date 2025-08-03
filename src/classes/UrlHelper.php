<?php

namespace Cronbeat;

class UrlHelper {
    /**
     * Parse the REQUEST_URI into an array of path segments
     * @return array<int, string>
     */
    private static function parseUri(): array {
        $uri = $_SERVER['REQUEST_URI'];
        $uri = trim($uri, '/');
        $uri = explode('/', $uri);
        return $uri;
    }
    
    public static function parseControllerFromUrl(): string {
        $uri = self::parseUri();
        return ($uri[0] !== '') ? $uri[0] : 'login';
    }
    
    public static function parsePathWithoutController(): string {
        $uri = self::parseUri();
        
        if (count($uri) > 1) {
            return implode('/', array_slice($uri, 1));
        }
        
        return '';
    }
}
