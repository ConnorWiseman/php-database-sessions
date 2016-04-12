<?php
/**
 * A basic wrapper for PHP's native PDO MySQL connection engine.
 */
final class DatabaseHandler {
    /**
     * @access private
     * @var object A PDO object.
     */
    private $db;

    /**
     * @access private
     * @var array An array of database connection credentials.
     * @todo This is temporary and cannot remain exposed in application code.
     */
    private $credentials = array(
        'host' => 'localhost',
        'db'   => 'test',
        'user' => 'root',
        'pass' => 'test'
    );

    /**
     * An alias for PDO::beginTransaction.
     *
     * @access public
     * @return $this->db::beginTransaction
     */
    public function beginTransaction() {
        return $this->db->beginTransaction();
    }

    /**
     * Connects to MySQL using PDO and the values in $this->credentials.
     *
     * @access public
     */
    public function __construct() {
        // Initialize a PDO object and assign it to $this->db.
        $this->db = new PDO(
            'mysql:host=' .
                $this->credentials['host'] . ';
            dbname=' .
                $this->credentials['db'],
                $this->credentials['user'],
                $this->credentials['pass'],
            // The database connection must use UTF-8 character encoding.
            array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8')
        );
        // PDO must always throw exceptions when it encounters errors.
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // PDO must always use actual prepared statements.
        $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }

    /**
     * An alias for PDO::exec.
     *
     * @access public
     * @param string $query The query to execute.
     * @return $this->db::exec
     */
    public function exec($query) {
        return $this->db->exec($query);
    }

    /**
     * An alias for PDO::lastInsertId.
     *
     * @access public
     * @return $this->db::lastInsertId
     */
    public function lastInsertId() {
        return $this->db->lastInsertId();
    }

    /**
     * An alias for PDO::prepare.
     *
     * @access public
     * @param string $query The query to prepare.
     * @return $this->db::prepare
     */
    public function prepare($query) {
        return $this->db->prepare($query);
    }
}