<?php
session_start();


// Get the URL from the query string (if no URL, default to authentication/index)
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'authentication/index';
$url = explode('/', $url);

// Build the file name (without namespace)
$controllerNameNoNS = ucfirst($url[0]) . 'Controller';
// Fully qualified class name including the namespace
$controllerName = 'App\\Controllers\\' . $controllerNameNoNS;


$methodName = isset($url[1]) ? $url[1] : 'index';
$params = array_slice($url, 2);

// Build the controller file path using the name without namespace
$controllerFile = '../app/controllers/' . $controllerNameNoNS . '.php';
if (file_exists($controllerFile)) {
    require_once $controllerFile;

    // Instantiate the controller class using the fully qualified name
    if (class_exists($controllerName)) {
        $controller = new $controllerName();

        // Check if the method exists in the controller
        if (method_exists($controller, $methodName)) {
            call_user_func_array([$controller, $methodName], $params);
        } else {
            echo "Method $methodName not found in $controllerName.";
        }
    } else {
        echo "Controller class $controllerName not found.";
    }
} else {
    echo "Controller file $controllerFile not found.";
}
