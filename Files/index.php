<?php

function my_autoloader($class) {
    include './' . $class . '.php';
}

spl_autoload_register('my_autoloader');

$user = new User;

/**
 * handle routing based on request path
 */
switch ($_SERVER['PATH_INFO']) {
    case '/login':
      $user->login();
      break;
    case '/register':
      $user->register();
      break;
    case '/getUsers':
      $user->getUsers();
      break;
    case '/sendMessage':
      $user->sendMessage();
      break;
    case '/viewMessages':
      $user->viewMessages();
      break;
    default:
      // default to index.php
      break;
}
