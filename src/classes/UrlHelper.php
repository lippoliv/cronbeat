<?php

namespace Cronbeat;

class UrlHelper {
    /**
     * Parse the controller name from the URL
     * 
     * @return string The controller name or 'login' if none is specified
     */
    public static function parseControllerFromUrl() {
        $uri = $_SERVER['REQUEST_URI'];
        $uri = trim($uri, '/');
        $uri = explode('/', $uri);
        return !empty($uri[0]) ? $uri[0] : 'login';
    }
}