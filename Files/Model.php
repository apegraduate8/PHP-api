<?php
/**
*
*/
class Model
{
    private $servername;
    private $username;
    private $password;
    private $dbname;
    private $conn;
    private $usertable = 'users';

    function __construct()
    {
        $this->conn = $this->connect();
    }

    /**
     * Make connection to mysql db
     *
     * @return (object) db object
     */
    protected function connect()
    {
        if (!$this->conn) {
            $this->host = '127.0.0.1:3306';
            $this->username = 'root';
            $this->password = '';
            $this->dbname = 'DA';
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->dbname);

            if (mysqli_connect_error() || !$this->conn) {
                printf("Connect failed: %s\n", mysqli_connect_error());
                exit();
            }
        }

        return $this->conn;
    }

    /**
     * Send json error response
     *
     * @param (string) $title - title of message
     * @param (string) $message - message
     * @return (object)  json error
     */
    public function setError($title, $message)
    {
        return json_encode([
            'error_code' => 101,
            'error_title' => $title,
            'error_message' => $message
          ]);
    }
}
