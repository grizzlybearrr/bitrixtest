<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

spl_autoload_register(function ($class_name) {
    include 'lib/'.$class_name.'.php';
});