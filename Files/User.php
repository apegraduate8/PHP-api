<?php
header('Content-type: application/json');
/**
*
*/
class User extends Model
{
  private $mysqli;

  protected $session;

  function __construct()
  {
    $this->mysqli = $this->connect();

    session_start();
  }

  // public function getMysqli() {
  //    return $this->mysqli;
  // }

  public function validate($register = null)
  {
      $email = $_POST['email'];
      $password = md5($_POST['password']);
      $first_name = $_POST['last_name'];
      $last_name = $_POST['last_name'];
      $authCheck = empty($email) || empty($password) ? false : true;
      $nameCheck = empty($last_name) || empty($first_name) ? false : true;

      if (!is_null($register)) {
          if (empty($email) || empty($password) || empty($last_name) || empty($first_name)) {
              return false;
          }

          // check if user exists
          $stmt = $this->mysqli->stmt_init();

          if (!$stmt->prepare("SELECT id FROM users WHERE email=? AND password=?")) {
              return false;
          }

          $stmt->bind_param('ss', $email, $password);
          $stmt->execute();

          /* bind result variables */
          $stmt->store_result();

          if($stmt->num_rows > 0) {
              return false;
          }

          return true;
      }

      if (empty($email) || empty($password)) {
        return false;
      }

      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
      }

      if (preg_match("/^[a-zA-Z]{3,12}$/", $password)) {
          // flash error
          return false;
      }

      return true;
  }

  public function register()
  {
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        // throw error, not post method
      //throw new Exception("Not a post method", 1);
      echo self::setError('Bad Registration', 'method not POST');
    }

    if (!$this->validate(true)) {
        echo self::setError('Not Valid', 'The information you entered is not valid. Please check all fields. The email/password might exist already');
    }

    // creation of new record
    $email = $_POST['email'];
    $password = md5($_POST['password']);
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $arr = [];
    $query = "INSERT INTO users (email, password, first_name, last_name) VALUES('$email', '$password', '$first_name', '$last_name')";

    if ($this->mysqli->connect_error) {
          die("Connection failed: " . $this->mysqli->connect_error);
    }

    if ($stmt = mysqli_prepare($this->mysqli, $query)) {
        $stmt->bind_param('ssss', $email, $password, $first_name, $last_name);
        $stmt->execute();

        if ($stmt->affected_rows == 1) {
            $arr[] = [
              'user_id' => mysqli_insert_id($this->mysqli),
              'email' => $email,
             'first_name' => $first_name,
             'last_name' => $last_name
           ];
        }

        echo json_encode($arr);

        // set session for user
    } else {
        echo self::setError('Not Inserted', 'Baddd');
    }
  }

  public function login()
  {
    if (!empty($_SESSION['email'])) {
        echo json_encode([$_SESSION]);
    }

    //throw error, request not post method
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
      //throw new Exception("Not a post method", 1);
       echo self::setError('Bad Login', 'method not POST');
    }

    // validate request body
    if (!$this->validate()) {
        // throw error, request not valid
      echo self::setError('Not Valid', 'The information you entered is not valid. Please check all fields');
    }

    // begin login
    $email = $_POST['email'];
    $password = md5($_POST['password']);
    $arr = [];

    $stmt = $this->mysqli->stmt_init();

    if (!$stmt->prepare("SELECT * FROM users WHERE email=? AND password=?")) {
        echo self::setError('SQL error', 'information could not be saved at this time');
    }

    $stmt->bind_param('ss', $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_array()) {
        $arr[] = [
        'user_id' => $row['id'],
        'first_name' => $row['first_name'],
        'last_name' => $row['last_name'],
        'email' => $row['email']
        ];
    }

    // free up resources
    $result->free();
    // if user login is successful then init session
    $this->initSession($arr[0]);
  }

  public function getUsers()
  {
    // throw error, not get method
    if ($_SERVER['REQUEST_METHOD'] != 'GET') {
      echo self::setError('bad request', 'method not GET');
    }

    $result = $this->mysqli->query('SELECT * FROM users', MYSQLI_USE_RESULT);

    if (count($result) > 0) {
        $data = null;
        while($row = $result->fetch_array()){
            $data[] = [
            'user_id' => $row['id'],
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'email' => $row['email']
            ];
        }

        echo json_encode($data);
    } else {
      // return error response
       echo json_encode([
          'error_code' => 101,
          'error_title' => 'getUsers request failure',
          'error_message' => 'Request failure'
        ]);
    }
  }

  public function initSession($data) {
    $_SESSION['user_id'] = $data['user_id'];
    $_SESSION['email'] = $data['email'];
    $_SESSION['first_name'] = $data['first_name'];
    $_SESSION['last_name'] = $data['last_name'];

    echo json_encode($_SESSION);
  }

  protected function getSession() {
    return [
    $_SESSION['user_id'],
    $_SESSION['email'],
    $_SESSION['first_name'],
    $_SESSION['last_name']
    ];
  }

  public function sendMessage()
  {
      if ($_SERVER['REQUEST_METHOD'] != 'POST') {
          // throw error, not post method
        echo self::setError('Message Error', 'method not POST');
      }

      if (empty($_SESSION['email'])) {
          echo self::setError('Session Error', 'must be logged in to send messages');
      }

      $sender = $_POST['sender_user_id'];
      $reciever = md5($_POST['reciever_user_id']);
      $message = $_POST['message'];

      if (empty($sender) || empty($sender) || empty($sender)) {
          echo self::setError('Message Error', 'please check the neccessary fields');
      }

      // save in DB

  }

  // viewMessage function

  function __destruct()
  {
    session_unset();
    session_destroy();

    if ($this->mysqli) {
        mysqli_close($this->mysqli);
    }
  }
}
