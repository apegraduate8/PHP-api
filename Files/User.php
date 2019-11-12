<?php
header('Content-type: application/json');
/**
* User class
* Handles all routes related to a user
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
     * Validates user inputs to determine if
     * user can initialize login or registration method
     *
     * @param (bool) $register - used to distinguish between login/registeration methoda
     * @return (bool)
     */
    public function validate($register = null)
    {
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost' => 12]);
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
     * Registration endpoint
     * Creates new record in DB
     *
     * @return (object) error | json object
     */
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
          //could throw new Exception("Not a post method", 1);
          echo self::setError('Bad Registration', 'method not POST');
        }

        if (!$this->validate(true)) {
            echo self::setError('Not Valid', 'The information you entered is not valid. Please check all fields. The email/password might exist already');
        }

        if ($this->mysqli->connect_error) {
              die("Connection failed: " . $this->mysqli->connect_error);
        }

        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
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

            $stmt->free();

            $this->initSession($arr[0]);
        } else {
            echo self::setError('Not Inserted', 'Baddd');
        }
    }

    /**
     * Login endpoint
     *
     * @return (object) error | json object
     */
    public function login()
    {
        // un-comment this part to validate session!
        if (!empty($_SESSION['email'])) {
            echo json_encode([$_SESSION]);
        }

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            echo self::setError('Bad Login', 'method not POST');
        }

        if (!$this->validate()) {
            echo self::setError('Not Valid', 'The information you entered is not valid. Please check all fields');
        }

        if ($this->mysqli->connect_error) {
              die("Connection failed: " . $this->mysqli->connect_error);
        }

        // begin login
        $email = $_POST['email'];
        $password = $_POST['password'];
        $query = "SELECT * FROM users WHERE email=?";
        $arr = [];

        if ($stmt = mysqli_prepare($this->mysqli, $query)) {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_array()) {
                if (password_verify($password, $row['password'])) {
                    $arr[] = [
                      'user_id' => $row['id'],
                      'first_name' => $row['first_name'],
                      'last_name' => $row['last_name'],
                      'email' => $row['email']
                    ];
                }
            }

            // if user login is successful then init session
            $this->initSession($arr[0]);
        } else {
            echo self::setError('SQL error', 'information could not be saved at this time');
        }
    }

    /**
     * List all users endpoint
     * Queriess db for all users excluding requester
     *
     * @return (object) error | json object
     */
    public function getUsers()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'GET') {
          echo self::setError('bad request', 'method not GET');
        }

        $request_user_id = $_GET['request_user_id'];
        $query = "SELECT * FROM `users` WHERE id <> ?";
        $arr = [];

        if ($stmt = mysqli_prepare($this->mysqli, $query)) {
            $stmt->bind_param('s', $request_user_id);
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

            echo json_encode($arr);
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
     * Get session data
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

    /**
     * Send message endpoint
     * Sends a message from one user to another
     *
     * @return (object) - error | json object
     */
    public function sendMessage()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            // throw error, not post method
            echo self::setError('Message Error', 'method not POST');

            return;
        }

        // un-comment this code to validate session!
        // if (empty($_SESSION['email'])) {
        //     echo self::setError('Session Error', 'must be logged in to send messages');

        //     return;
        // }

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

    /**
     * View message endpoint
     * Returns all messages between two users in date order.
     *
     * @return (object) - serror | json object
     */
    public function viewMessages()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'GET') {
            echo self::setError('bad request', 'method not GET');
        }

        // un-comment this part to validate session!
        // if (empty($_SESSION['email'])) {
        //     echo self::setError('Session Error', 'must be logged in to send messages');
        // }

        $user_id_a = $_GET['user_id_a'];
        $user_id_b = $_GET['user_id_b'];

        if (empty($user_id_a) || empty($user_id_b)) {
            echo self::setError('Message Request Error', 'please check the required parameters');
        }

        if ($this->mysqli->connect_error) {
            die("Connection failed: " . $this->mysqli->connect_error);
        }

        $arr = [];
        $query = "SELECT * FROM `messages` WHERE sender_user_id IN (?,?) AND recipient_id IN (?,?) ORDER BY epoch";

        if ($stmt = mysqli_prepare($this->mysqli, $query)) {
            $stmt->bind_param('ssss', $user_id_a, $user_id_b, $user_id_b, $user_id_a);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_array()) {
                $arr[] = [
                  'message_id' => $row['id'],
                  'sender_user_id' => $row['sender_user_id'],
                  'message' => $row['message'],
                  'epoch' => $row['epoch'],
               ];
            }

            echo json_encode($arr);
        } else {
            echo self::setError('SQL error!', 'Cannot retreive Messages at this time');
        }
    }

    function __destruct()
    {
        session_unset();
        session_destroy();

        if ($this->mysqli) {
            mysqli_close($this->mysqli);
        }
    }
}
