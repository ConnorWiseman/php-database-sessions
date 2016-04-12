<?php
/**
 * A class for handling PHP sessions in a MySQL database.
 *
 * @author Connor Wiseman <wiseman.connor@gmail.com>
 * @version 03.6.2014
 * @copyright 2013-2014, Connor Wiseman
 */
final class User {
    /**
     * @access private
     * @var object A reference to a DatabaseHandler object.
     */
    private $db;

    /**
     * @access public
     * @var array Information in the current session.
     * @todo Keeping the password hash in this array is a security risk.
     */
    private $user_info = Array(
        'id'	=> null,
		'email'		=> null,
        'username'	=> null,
		'password'	=> null
    );

    /**
     * The class constructor function.
     *
     * @access public
     * @param object $db A reference to a DatabaseHandler object.
     * @param mixed $user_id This user's ID, inherited from a Session object.
     */
    public function __construct(DatabaseHandler $db, $user_id = null) {
        $this->db = $db;
		if($this->loadUser($user_id)) {

		} else {

		}
    }

    /**
     * Checks to see whether a given user exists in the database and
     * whether $_POST['password'] is a match for the stored password hash.
     *
     * @access public
     * @return bool Success or failure.
     */
	public function authenticate() {
		if(isset($_POST['email']) && isset($_POST['password'])) {
			if(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
				if(is_null($this->user_info['id'])) {
					$query = $this->db->prepare('
						SELECT id, password
						FROM users
						WHERE email = :email
						LIMIT 1
					');
					$query->bindParam(':email', $_POST['email'], PDO::PARAM_STR);
					if($query->execute()) {
						$result = $query->fetch(PDO::FETCH_ASSOC);
						if(password_verify($_POST['password'], $result['password'])) {
							$this->user_info['id'] = $result['id'];
							return true;
						}
					}
				}
			}
		}
		return false;
	}

    /**
     * Checks to see whether a given user exists in the database and
     * whether $_POST['password'] is a match for the stored password hash.
     *
     * @access public
     * @return bool Success or failure.
     */
	public function create() {
		if(isset($_POST['email']) && isset($_POST['username']) && isset($_POST['password'])) {
			if(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
				if(is_null($this->user_info['id'])) {
					$password = password_hash($_POST['password'], PASSWORD_DEFAULT, ['cost' => 8]);
					$query = $this->db->prepare('
						INSERT INTO users
							(email, username, password)
						VALUES
							(:email, :username, :password)
					');
					$query->bindParam(':email', $_POST['email'], PDO::PARAM_STR);
					$query->bindParam(':username', $_POST['username'], PDO::PARAM_STR);
					$query->bindParam(':password', $password, PDO::PARAM_STR, 60);
					if($query->execute()) {
						$this->user_info['id'] = $this->db->lastInsertId();
						return true;
					}
				}
			}
		}
		return false;
	}

    /**
     * Getter method for reading $this->user_info.
     *
     * @access public
     * @param $key string The key in $this->user_info to retrieve.
     * @return string $this->user_info[$key]
     */
    public function get($key) {
        return $this->user_info[$key];
    }

    /**
     * Reads the current user's info into $this->user_info.
     *
     * @access private
     * @param string $user_id The user ID to read from the database.
     * @return bool Success or failure.
     */
    private function loadUser($user_id) {
		if(!is_null($user_id)) {
            $query = $this->db->prepare('
                SELECT id, email, username, password
                FROM users
                WHERE id = :id
                LIMIT 1
            ');
            $query->bindParam(':id', $user_id, PDO::PARAM_INT);
            if($query->execute()) {
				$this->user_info = $query->fetch(PDO::FETCH_ASSOC);
				return true;
			} else {
				throw new Exception('Database user read failed.');
			}
		}
		return false;
	}

    /**
     * Returns the contents of $this->user_info.
     * Temporary debugging tool.
     *
     * @access public
     */
	public function userInfo() {
		return '<pre>' . print_r($this->user_info, true) . '</pre>';
	}
}