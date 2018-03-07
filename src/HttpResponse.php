<?php

namespace OgreWeb\Lib;

class HttpResponse{

    static function success($data = null){
        header('HTTP/1.1 200 Ok');
        header('Content-Type: application/json');
        return json_encode($data);
    }

    static function unauthorized(){
        header('HTTP/1.1 401 Unauthorized');
    }

    static function teaPot(){
        header("HTTP/1.1 418 I’m a teapot");
    }

    static function internalError(){
        header('HTTP/1.1 500 Internal Error');
    }

    static function notFound(){
        header('HTTP/1.1 404 Not found');
    }

    static function badRequest(){
        header('HTTP/1.1 400 Bad Request');
    }
}
?>
