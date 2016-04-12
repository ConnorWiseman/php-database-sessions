<?php
/**
 * A class for handling PHP sessions in a MySQL database.
 */
final class Session implements SessionHandlerInterface {
    /**
     * @access private
     * @var object A reference to a DatabaseHandler object.
     */
    private $db;

    /**
     * @access private
     * @var array Information in the current session.
     */
    private $session_info = Array(
        'id' => null,
        'auth_key'   => null,
        'user_id'    => null,
        'ip_address' => null,
        'accessed'   => null
    );

    /**
     * The class constructor function.
     *
     * @access public
     * @param object $db A reference to a DatabaseHandler object.
     * @todo We're not supposed to change ini settings in a file like this.
     */
    public function __construct(DatabaseHandler $db) {
        $this->db = $db;
        // Set the session name.
        session_name('session_id');
        // Change some settings for additional security.
        ini_set('session.cookie_httponly', 1);
        ini_set('session.entropy_file', '/dev/urandom');
        ini_set('session.entropy_length', '1024');
        ini_set('session.gc_divisor', '100');
        ini_set('session.gc_probability', '1');
        ini_set('session.gc_maxlifetime', '1500');
        ini_set('session.hash_bits_per_character', '6');
        ini_set('session.hash_function', 'sha512');
        // Adjust PHP's default session handler to use Session instead.
        session_set_save_handler($this, true);
        // Adjust the default session cookie settings.
        session_set_cookie_params(1500, '/', '.104.197.17.161', false, true);
        // Begin a session.
        session_start();
    }

    /**
     * Explicitly cuts off Session's access to the database.
     * Does not terminate DatabaseHandler's connection.
     * SessionHandlerInterface::close
     *
     * @access public
     * @return true
     */
    public function close() {
        $this->db = null;
        return true;
    }

    /**
     * Creates a new auth key.
     *
     * @access private
     * @return string A new auth key.
     */
    private function authKey() {
        return bin2hex(openssl_random_pseudo_bytes(16));
    }

    /**
     * Compares $_POST['auth_key'] to the current auth key and returns
     * whether a match exists.
     *
     * @access private
     * @return bool Match or no match.
     */
    public function authKeyCompare() {
        if (isset($_POST['auth_key'])) {
            return $this->session_info['auth_key'] === $_POST['auth_key'];
        }
    }

    /**
     * Removes the session from the database and destroys associated cookies.
     * SessionHandlerInterface::destroy
     *
     * @access public
     * @param $id string The session ID. Used.
     * @return true on success.
     */
    public function destroy($id) {
        $query = $this->db->prepare('
            DELETE FROM sessions
            WHERE id = :id
            LIMIT 1
        ');
        $query->bindParam(':id', $id, PDO::PARAM_STR, 86);
        if ($query->execute()) {
            setcookie('session_id', '', time() - 3600, '/', '.104.197.17.161', false, true);
            return true;
        } else {
            return false;
            throw new Exception('Database session destroy failed.');
        }
    }

    /**
     * Removes old sessions from the database.
     * SessionHandlerInterface::gc
     *
     * @access public
     * @param $lifetime int The maximum lifetime. Used.
     * @return bool Success or failure.
     */
    public function gc($maxlifetime) {
        $expiry = time() - $maxlifetime;
        $query = $this->db->prepare('
            DELETE FROM sessions
            WHERE accessed < :lifetime
        ');
        $query->bindParam(':lifetime', $expiry, PDO::PARAM_INT);
        return $query->execute();
    }

    /**
     * Getter method for reading $this->session_info.
     *
     * @access public
     * @param $key string The key in $this->session_info to retrieve.
     * @return string $this->session_info[$key]
     */
    public function get($key) {
        return $this->session_info[$key];
    }

    /**
     * Converts the client's IP address to an integer for database storage.
     *
     * @access private
     * @todo Needs to actually fetch an IP. Doesn't seem to work, even with
     *       proper headers accounted for.
     * @return int The client's IP address as an integer.
     */
    private function ip() {
        $ipaddress = getenv('HTTP_CLIENT_IP')?:
            getenv('HTTP_X_FORWARDED_FOR')?:
            getenv('HTTP_X_FORWARDED')?:
            getenv('HTTP_FORWARDED_FOR')?:
            getenv('HTTP_FORWARDED')?:
            getenv('REMOTE_ADDR');
        return ip2long($ipaddress);
    }

    /**
     * Begins a session.
     * SessionHandlerInterface::open
     *
     * @access public
     * @param string $path The save path of the session. Not used.
     * @param string $name The name of the session. Not used.
     * @return bool $this->db exists.
     */
    public function open($path, $name) {
        if($this->db) {
            return true;
        }
        return false;
    }

    /**
     * Reads a session from database; updates IP address and last access date.
     * SessionHandlerInterface::read
     *
     * @access public
     * @param string $id The session ID. Used.
     * @return string An empty string- required, or so says PHP documentation.
     */
    public function read($id) {
        if(strlen($id) === 86) {
            $this->sessionUpdate($id, $this->session_info['user_id']);
            $this->sessionLoad($id);
            return '';
        } else {
            throw new Exception('Session id invalid.');
        }
        return false;
    }

    /**
     * Retrieves a session record from the database and adds it to
     * the $this->session_info array.
     *
     * @access private
     * @param string $id The session ID.
     * @return true on success.
     */
    private function sessionLoad($id) {
        $query = $this->db->prepare('
            SELECT
                id, auth_key, user_id, ip_address, accessed
            FROM sessions
            WHERE id = :id
            LIMIT 1
        ');
        $query->bindParam(':id', $id, PDO::PARAM_STR, 86);
        if($query->execute()) {
            $this->session_info = $query->fetch(PDO::FETCH_ASSOC);
            return true;
        } else {
            throw new Exception('Session load failed.');
        }
    }

    /**
     * Creates a new session record in the database. Alternatively, updates
     * the IP address and last access date for the current session.
     *
     * @access private
     * @param string $id The session ID to update.
     * @param int $user_id A user ID to bind to the session.
     * @return true on success.
     */
    private function sessionUpdate($id, $user_id = null) {
        $auth_key = $this->authKey();
        $ip_address = $this->ip();
        // IP definitely exists. Dunno why it's recorded 0.
        $accessed = time();
        $query = $this->db->prepare('
            INSERT INTO sessions
                (id, auth_key, user_id, ip_address, accessed)
            VALUES
                (:id, :auth_key, :user_id, :ip_address1, :accessed1)
            ON DUPLICATE KEY UPDATE
                ip_address = :ip_address2, accessed = :accessed2
        ');
        $query->bindParam(':id', $id, PDO::PARAM_STR, 86);
        $query->bindParam(':auth_key', $auth_key, PDO::PARAM_STR, 32);
        $query->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $query->bindParam(':ip_address1', $ip_address, PDO::PARAM_INT);
        $query->bindParam(':accessed1', $accessed, PDO::PARAM_INT);
        $query->bindParam(':ip_address2', $ip_address, PDO::PARAM_INT);
        $query->bindParam(':accessed2', $accessed, PDO::PARAM_INT);
        if($query->execute()) {
            return true;
        } else {
            throw new Exception('Session update failed.');
        }
    }

    /**
     * Returns the contents of $this->session_info.
     * Temporary debugging tool.
     *
     * @access public
     */
    public function sessionInfo() {
        return '<pre>' . print_r($this->session_info, true) . '</pre>';
    }

    /**
     * Logs a user into a session.
     *
     * @access public
     * @param int $user_id The numeric ID of the user to log in.
     * @return bool Success or failure.
     */
    public function sessionLogin($user_id) {
        if(!is_null($user_id) && is_null($this->session_info['user_id'])) {
            session_regenerate_id();
            $session_id = session_id();
            $this->sessionUpdate($session_id, $user_id);
            $query = $this->db->prepare('
                UPDATE sessions
                SET user_id = :user_id
                WHERE id = :id AND user_id IS NOT NULL
                LIMIT 1
            ');
            $query->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $query->bindParam(':id', $session_id, PDO::PARAM_STR, 86);
            if($query->execute()) {
                $this->session_info['user_id'] = $user_id;
                header('Location: http://104.197.17.161/');
                exit;
            } else {
                throw new Exception('Session auth key replacement failed.');
            }
        }
        return false;
    }

    /**
     * Logs a user out of a session.
     *
     * @access public
     * @return bool Success or failure.
     */
    public function sessionLogout() {
        if(isset($_POST['auth_key'])) {
            if(!is_null($this->session_info['user_id'])) {
                session_regenerate_id();
                $session_id = session_id();
                $this->sessionUpdate($session_id);
                if(isset($_POST['no_redirect']) && $_POST['no_redirect'] === true) {
                    return true;
                } else {
                    header('Location: http://104.197.17.161/');
                    exit;
                }
            }
        }
        return false;
    }

    /**
     * Removes other sessions with the same user_id as the current session.
     *
     * @access public
     * @return bool Success or failure.
     */
    public function sessionRemoveDuplicates() {
        if(!is_null($this->session_info['user_id'])) {
            $query = $this->db->prepare('
                DELETE FROM sessions
                WHERE user_id = :user_id
                AND id <> :id
            ');
            $query->bindParam(':user_id', $this->session_info['user_id'], PDO::PARAM_INT);
            $query->bindParam(':id', $this->session_info['id'], PDO::PARAM_STR, 86);
            return $query->execute();
        }
        return false;
    }

    /**
     * No dependency on $_SESSION, so not used. Writing is handled elsewhere.
     * SessionHandlerInterface::write
     *
     * @access public
     * @param string $id The session ID. Not used.
     * @param string $data Data to be written to the session. Not used.
     * @return true
     */
    public function write($id, $data) {
        return true;
    }
}