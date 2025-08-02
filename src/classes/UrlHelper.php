<?php

namespace Cronbeat;

class UrlHelper {
    public static function parseControllerFromUrl(): string {
        $uri = $_SERVER['REQUEST_URI'];
        $uri = trim($uri, '/');
        $uri = explode('/', $uri);
        return ($uri[0] !== '') ? $uri[0] : 'login';
    }
}
