<?php

namespace Cronbeat;

class UrlHelper {
    public static function parseControllerFromUrl(): string {
        $uri = $_SERVER['REQUEST_URI'];
        $uri = trim($uri, '/');
        $uri = explode('/', $uri);
        return ($uri[0] !== '') ? $uri[0] : 'login';
    }
    
    public static function parsePathWithoutController(): string {
        $uri = $_SERVER['REQUEST_URI'];
        $uri = trim($uri, '/');
        $uri = explode('/', $uri);
        
        if (count($uri) > 1) {
            return implode('/', array_slice($uri, 1));
        }
        
        return '';
    }
}
