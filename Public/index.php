<?php

session_start();

use App\Libraries\Request;
use App\Libraries\ErrorHandler;

// Composer autoload => composer dumpautoload
require_once '../vendor/autoload.php';

// Global Constants
require_once '../app/Config/global_constants.php';

// Require all helpers
require_once '../App/helpers/redirect.php';
require_once '../App/helpers/view.php';
require_once '../App/helpers/flash.php';
require_once '../App/helpers/old.php';
require_once '../App/helpers/print_r2.php';
require_once '../App/helpers/date.php';
require_once '../App/helpers/auth.php';
require_once '../App/helpers/session.php';
require_once '../App/helpers/uploader.php';
require_once '../App/helpers/paginator.php';
require_once '../App/helpers/csrf_token.php';

/*
 * Router File
 * URL FORMAT (Controller) - controller/method/params
 * URL FORMAT (ApiController) - api/controller/method/params
 */

class Router
{
    private $currentController = "HomeController";
    private $currentMethod = "index";
    private $url;

    public function __construct(Request $request)
    {
        // set current URL.
        $this->_setUrl(); 

        // if there is no URL, call default Controller and Method.
        if(!$this->url){
            $controller = $this->_getController();
            $scope = $this->_getScopeMethod($controller, $this->currentMethod);
            if($scope === 'private'){
                return view("errors/page_404", [], 404);
            }
            return call_user_func_array([$controller, $this->currentMethod], [$request]);
        }
        
        // Extract controller's name from the URL.
        $this->_setController();

        // Get the instince of that controller if exists, otherwise render NOT FOUND page.
        $controller = $this->_getController();

        /*
          * Get the method of controller if exists, otherwise render NOT FOUND page
          * PS: if the method doesn't exists, it will look for index method, if exists it calls it and 
          * pass the value that used to check to index method, otherwise NOT FOUND page rendered
        */

        $method = $this->_getMethod($controller);
        $scope = $this->_getScopeMethod($controller, $method);
        if($scope === 'private'){
            return view("errors/page_404", [], 404);
        }

        $params = $this->_getParams();
        
        array_unshift($params, $request);
        return call_user_func_array([$controller, $method], $params);
    }

    private function _getParams()
    {   
        return $this->url ? array_values($this->url) : [];
    }

    private function _getScopeMethod($controller, $method)
    {
        $reflectionMethod = new \ReflectionMethod($controller, $method);
        $visibility = \Reflection::getModifierNames($reflectionMethod->getModifiers());
        return $visibility[0];
    }

    private function _getMethod($controller)
    {
        $method = $this->url[1] ?? 'index';
        if(!method_exists($controller, $method)){
            if(method_exists($controller, 'index')){
                $this->currentMethod = 'index';
            }else{
                return view("errors/page_404", [], 404);
            }
        }else{
            $this->currentMethod = $method;
            unset($this->url[1]);
        }

        return $this->currentMethod;
    }

    private function _setController()
    {
        if($this->url[0] === 'api'){
            $this->currentController = "Api\\".trim(ucfirst($this->url[1]), 's')."Controller";
            unset($this->url[1]);
        }else{
            $this->currentController = ucfirst($this->url[0])."Controller";
        }
        unset($this->url[0]);
    }

    private function _getController()
    {
        $controllerClassName = "\App\Controllers\\" . $this->currentController;
        
        if (!class_exists($controllerClassName)) {
            if(str_contains($controllerClassName, "Api")){
                return \App\Libraries\Response::json(null, 404, "Route not found ");
            }
            return view("errors/page_404", [], 404);
        }
        return new $controllerClassName;
    }

    private function _setUrl()
    {
        if (isset($_GET["url"])) {
            $url = rtrim($_GET["url"], "/");
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode("/", $url);
            $this->url = $url;
        }
    }
}

set_exception_handler([ErrorHandler::class, 'handleException']);
set_error_handler([ErrorHandler::class, 'handleError']);

$init = new Router(new Request);