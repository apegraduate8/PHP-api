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

  /**
   * Validates user inputs
   * to determine if user can initialize login or registration method
   *
   * @param (bool) $register - used to distinguish between login/registeration methoda
   * @return (bool)
   */
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

          $stmt = $this->mysqli->stmt_init();

          if (!$stmt->prepare("SELECT id FROM users WHERE email=? AND password=?")) {
              return false;
          }

          $stmt->bind_param('ss', $email, $password);
          $stmt->execute();
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
          return false;
      }

      return true;
  }

  /**
   * Registration action
   *
   * @return (object) error | json object
   */
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
    if ($this->mysqli->connect_error) {
          die("Connection failed: " . $this->mysqli->connect_error);
    }

    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost' => 12]);
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $arr = [];
    $query = "INSERT INTO users (email, password, first_name, last_name) VALUES(?, ?, ?, ?)";

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

  /**
   * Login action
   *
   * @return (object) error | json object
   */
  public function login()
  {
    if (!empty($_SESSION['email'])) {
        echo json_encode([$_SESSION]);
    }

    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        echo self::setError('Bad Login', 'method not POST');
    }

    if (!$this->validate()) {
        echo self::setError('Not Valid', 'The information you entered is not valid. Please check all fields');
    }

    // begin login
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost' => 12]);
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

  /**
   * getUsers endpoint
   * queriess db for all users
   *
   * @return (object) error | json object
   */
  public function getUsers()
  {
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

  /**
   * Saves data to user session
   *
   * @return (object) - json object
   */
  public function initSession($data)
  {
    $_SESSION['user_id'] = $data['user_id'];
    $_SESSION['email'] = $data['email'];
    $_SESSION['first_name'] = $data['first_name'];
    $_SESSION['last_name'] = $data['last_name'];

    echo json_encode($_SESSION);
  }

  /**
   * Session data
   *
   * @return (object) - session data as object
   */
  protected function getSession()
  {
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

          return;
      }

      if (empty($_SESSION['email'])) {
          echo self::setError('Session Error', 'must be logged in to send messages');

          return;
      }

      $sender = $_POST['sender_user_id'];
      $recipient = $_POST['receiver_user_id'];
      $message = $_POST['message'];

      if (empty($sender) || empty($recipient) || empty($message)) {
          echo self::setError('Message Error', 'please check the required fields');

          return;
      }

      if ($this->mysqli->connect_error) {
          die("Connection failed: " . $this->mysqli->connect_error);
      }
      // save in DB

      $arr = [];
      $query = "INSERT INTO messages (sender_user_id, recipient_id, message) VALUES(?, ?, ?)";

      if ($stmt = mysqli_prepare($this->mysqli, $query)) {
          $stmt->bind_param('sss', $sender, $recipient, $message);
          $stmt->execute();

          if ($stmt->affected_rows == 1) {
              $arr[] = [
                'success_code' => 200,
                'success_title' => 'Message Sent',
               'success_message' => 'Message was sent successfully',
             ];
          }

          echo json_encode($arr);
      } else {
          echo self::setError('SQL error!', 'Message not saved');
      }
  }

  // public function viewMessage()
  // {
  //     if ($_SERVER['REQUEST_METHOD'] != 'GET') {
  //         echo self::setError('bad request', 'method not GET');
  //     }

  //     if (empty($_SESSION['email'])) {
  //         echo self::setError('Session Error', 'must be logged in to send messages');
  //     }

  //     if (empty($user_id_a) || empty($user_id_b)) {
  //         echo self::setError('Message Request Error', 'please check the required parameters');
  //     }

  //     // make db call

  // }

  function __destruct()
  {
    session_unset();
    session_destroy();

    if ($this->mysqli) {
        mysqli_close($this->mysqli);
    }
  }
}
