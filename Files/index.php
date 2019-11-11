<?php
// WORKS!
//var_dump(parse_url($_SERVER['REQUEST_URI']));
//var_dump($_SERVER['PATH_INFO']);
// $parts = parse_url($_SERVER['REQUEST_URI']);

function my_autoloader($class) {
    include './' . $class . '.php';
}

spl_autoload_register('my_autoloader');

$user = new User;

// handle routing
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
