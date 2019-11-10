<?php

/**
*
*/
class Test extends Model
{
  private $username;
  private $password;
  private $email;
  private $mysqli;

  function __construct(argument)
  {
    $this->mysqli = $this->connect();
  }

  function initSession($username, $password, $email) {
    $this->username = $username;
    $this->password = $password;
    $this->email = $email;
  }

  function validate()
  {
      $username = $_POST['username'];
      $email = $_POST['email'];
      $password = $_POST['password'];

      if (empty($username) || empty($email) || empty($password)) {
        // flash error
      }

      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          // flash error
      }

      if (preg_match('/^[a-z0-9_-]{3,15}$/', $username)) {
          // flash error
      }
  }

  function register()
  {
    $this->validate();


    // if user is created successfully then init session
    $this->initSession($username, $password, $email)
  }

  function login()
  {

    if ($this->session) {
        // redirect user to home/account page
    }

    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $this->mysqli->prepare("SELECT id FROM users WHERE email=? AND password=?";);
    $stmt->bind_param('ss', $email, $password);

    if (!$stmt) {
      //throw error
    }

    // $sql = "SELECT id FROM users WHERE email=? AND password=?";
    // $st = mysqli_stmt_init($this->connect());

    // if (mysqli_stmt_prepare($st, $sql)) {
    //   // throw error
    //   exit();
    // }

    mysqli_stmt_bind_param


    // if user is created successfully then init session
    $this->initSession($username, $password, $email)
  }

  function sendMessage()
  {

  }

  function readMessages()
  {

  }

  function listUsers()
  {
    $list = $this->getUsers();

    if (count($list) > 0) {
      // configure data for api response
    } else {
      // return error response

        return {
          “error_code”: 101
          “error_title”: “Login Failure”
          “error_message”: “Email or Password was Invalid!”
        }
    }
  }
}

public function getUsers()
  {
    // throw error, not get method
    if ($_SERVER['REQUEST_METHOD'] != 'GET') {
      header('Content-type: application/json');
      echo $this->setError('bad request', 'method not GET');
    }

    // $sql = 'SELECT * FROM' . $this->usertable;
    // $connection = $this->conn;
    // $result = $connection->$query($sql);

    // if (count($result) > 0) {
    //   $data = null;
    //   //iterate over list
          // while($row = $result->fetch_row()){
          //     echo $row[0] . '<br>';
          //     $data[] = [
          //       'user_id' => $item['id'],
          //       'first_name' => $item['first_name'],
          //       'last_name' => $item['last_name'],
          //       'email' => $item['email'],
          //     ];
          // }
    //   foreach ($result->fetch_assoc() as $item) {
    //     $data[] = [
    //       'user_id' => $item['id'],
    //       'first_name' => $item['first_name'],
    //       'last_name' => $item['last_name'],
    //       'email' => $item['email'],
    //     ];
    //   }

    //   mysql_close($connection);

    //   return json_encode(['users' => $data]);
    // } else {
    //   mysql_close($connection);
    //   // return error response
    //   json_encode([
    //       “error_code” => 101
    //       “error_title” => “Login Failure”
    //       “error_message” => “Email or Password was Invalid!”
    //     ])
    // }
  }
