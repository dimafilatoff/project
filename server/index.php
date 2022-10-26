<?php
if (isset($token)) {
    // проверка токена и получение роли
    $role = 1;
    switch ($role) {
        case 1:
            require_once("admin.php");
            break;
        case 6:
            require_once("admin.php");
            break;
        case 2:
            require_once("resident.php");
            break;
        case 3:
            require_once("worker.php");
            break;
        case 4:
            require_once("guard.php");
            break;
    }
} else {
    require_once("home.php");
}