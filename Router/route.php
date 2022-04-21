<?php

namespace Router;

use functions\main;

class Route extends main {

    public static function uri($reservedUrl, $class, $method, $requestMethod = "GET"){
        //current url
        $currentUrl = explode('?', currentUrl())[0];
        $currentUrl = str_replace(CURRENT_DOMAIN, '', $currentUrl);
        $currentUrl = trim($currentUrl, '/');
        $currentUrlArray = explode('/', $currentUrl);
        $currentUrlArray = array_filter($currentUrlArray);
        $copyCurrentUrl = $currentUrlArray;
        //reserved url 
        $reservedUrl = trim($reservedUrl, '/');
        $reservedUrlArray = explode('/', $reservedUrl);
        $reservedUrlArray = array_filter($reservedUrlArray);
        $copyReservedURL = $reservedUrlArray;
        foreach($copyReservedURL as $item){
            if ($item[strlen($item)-2] === '?'){
                $index =array_search($item,$copyReservedURL);
                unset($copyReservedURL[$index]);
                unset($copyCurrentUrl[$index]);
            }
        }
        if(sizeof($copyReservedURL) == sizeof($copyCurrentUrl))
        {
            if (methodField() !== $requestMethod){
                if (gettype($requestMethod) === "array"){
                    if (!in_array(methodField(),$requestMethod)){
                        return false;
                    }    
                }
                else {
                    return false;
                }
            }
        }
        else {
            return false;
        }
        // admin/category/edit/{id}/test reserved
        // ['amdin', 'category', 'edit', '{id}']
        // admin/category/hassan/3/test current url
           // ['amdin', 'category', 'edit', '3']
        $parameters = [];
        for($key = 0; $key < sizeof($currentUrlArray); $key++)
        {
            if($reservedUrlArray[$key][0] == '{' && $reservedUrlArray[$key][strlen($reservedUrlArray[$key]) - 1] == '}')
            {
                if ($reservedUrlArray[$key][strlen($reservedUrlArray[$key]) - 2] === "?"){
                    if ($currentUrlArray[$key] === ""){
                        array_push($parameters, null);
                    }
                    else {
                        array_push($parameters, $currentUrlArray[$key]);
                    }
                }
                else {
                    array_push($parameters, $currentUrlArray[$key]);
                }
            }
            elseif($currentUrlArray[$key] !== $reservedUrlArray[$key])
            {
                return false;
            }
        }
        if (count($reservedUrlArray) !== 0){
            if ($reservedUrlArray[0] === 'api'){
                $bladeClass = explode("\\",$class);
                $bladeClass[count($bladeClass ) - 1] = end($bladeClass) . '.php';
                $path = $_SERVER['DOCUMENT_ROOT'] . "/".folder_name."/". implode('/',$bladeClass);    
            }
            else {
                $bladeClass = explode("\\",$class);
                $bladeClass[count($bladeClass ) - 1] = end($bladeClass) . '.php';
                $path = $_SERVER['DOCUMENT_ROOT'] . "/".folder_name."/" . "activities/". implode('/',$bladeClass);    
            }        
        }
        else {
            $bladeClass = explode("\\",$class);
            $bladeClass[count($bladeClass ) - 1] = end($bladeClass) . '.php';
            $path = $_SERVER['DOCUMENT_ROOT'] . "/".folder_name."/" . "activities/". implode('/',$bladeClass);    
        }
        if (file_exists($path)){
            require_once($path);
        }
        else {
            $newValue = $bladeClass[0] . '-page';
            $bladeClass[0] = $newValue;
            $path = $_SERVER['DOCUMENT_ROOT'] . "/".folder_name."/" . "activities/". implode('/',$bladeClass);
            require_once($path);
        }
        if(methodField() == 'POST' && $reservedUrlArray[0] != "api")
        {
            $request = isset($_FILES) ? array_merge($_POST, $_FILES) : $_POST;
            $parameters = array_merge([$request], $parameters);
        }
            // category/create
            if (method_exists($class,$method)){
                $object = new $class;
                call_user_func_array(array($object, $method), $parameters);
                exit;    
            }
            else {
                return false;
            }
    }    
}
?>