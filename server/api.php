<?php
include_once($_SERVER['DOCUMENT_ROOT'] . "/Planix.php");
$app = new Planix();


switch ($_GET['action']) {
    case "login" :
        $json = $app->login(
            $_POST['email'],
            $_POST['password']
        );
        break;

    case "register" :
        $json = $app->register(
            $_POST['name'],
            $_POST['email'],
            $_POST['password']
        );
        break;
    case "profile" :
        $json = $app->profile(
            $_GET['token']
        );
        break;
    case "users" :
        $json = $app->users(
            $_GET['filter']
        );
        break;
    case "tickets" :
        $json = $app->tickets();
        break;
}
if(isset($json)) {
    header('Content-Type: application/json');
    echo json_encode($json);
}