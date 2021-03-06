<?php

require_once "Database.php";

class User {
    private static $db;
    private static $current_user;
    private $authenticated = false;
    private $id = 0;
    public $name = "";
    public $username = "";
    private $password = "";
    public $admin = false;
    public $pop_id = 0;
    public $avatar = "";
    
    private static function do_db_work()
    {
        if (!self::$db) {
            self::$db = Database::get();
            self::$db->prepare("get_user_by_username", "SELECT * FROM user WHERE alias=?");
            self::$db->prepare("create_user", "INSERT INTO user (name, alias, password) VALUES (?, ?, ?)");
            self::$db->prepare("save_user", "UPDATE user SET name=:name, alias=:username, admin=:admin, pop_id=:pop_id, avatar=:avatar WHERE id=:id");
            self::$db->prepare("change_user_password", "UPDATE user SET password=:password WHERE id=:id");
        }
    }
    
    /**
     * Returns the User object of the user that's currently logged in.
     */
    public static function get_current()
    {
        if (self::$current_user) {
            return self::$current_user;
        }
        return false;
    }
    
    /**
     * Creates a User object for the user currently logged in.
     * If there is no user currently logged in, does nothing.
     */
    public static function from_session()
    {
        if (array_key_exists('username', $_SESSION)) {
            self::$current_user = new User($_SESSION['username']);
            self::$current_user->authenticated = true;
            return self::$current_user;
        }
        return false;
    }
    
    /**
     * Loads a user's data from the database.
     * This DOES NOT authenticate the user.
     * It also does not log the user in.
     * A User object is not meant to represent the currently logged in
     * user, but ANY user in the system.
     */
    public function __construct($username)
    {
        self::do_db_work();
        self::$db->bind("get_user_by_username", 1, $username);
        $result = self::$db->getOne("get_user_by_username");
        $this->id = $result['id'];
        $this->username = $username;
        $this->name = $result['name'];
        $this->admin = $result['admin'];
        $this->avatar = $result['avatar'];
        $this->pop_id = $result['pop_id'] ? $result['pop_id'] : 0;
        $this->password = $result['password'];
        $this->authenticated = false;
    }
    
    /**
     * Saves a user's data to the database.
     * Only the public fields are changed.
     */
    public function __destruct()
    {
        $this->save();
    }
    
    /**
     * Checks that the user's supplied password matches the one in the
     * database. Note that if the password matches, this user will be made
     * the currently logged in user.
     * The password passed to this function must be the plain text password.
     */
    public function authenticate($password)
    {
        if (password_verify($password, $this->password)) {
            if (self::$current_user) {
                self::$current_user->authenticated = false;
            }
            $this->authenticated = true;
            self::$current_user = $this;
            $_SESSION['username'] = $this->username;
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Logs the user out.
     */
    public function log_out() {
        if (!$this->authenticated) return;
        self::$current_user = null;
        $this->authenticated = false;
        $_SESSION['username'] = null;
        session_destroy();
    }
    
    /**
     * Changes the user's password.
     * This has no safety measures; verifying the user, making sure the password
     * is entered correctly, and all such checks must be made before this call.
     * However, the user WILL need to be authenticated.
     */
    public function change_password($newpassword)
    {
        if (!$this->authenticated) {
            throw new Exception("Not authenticated");
        }
        $this->password = password_hash($newpassword, PASSWORD_DEFAULT);
        self::$db->bind("change_user_password", ":id", $this->id);
        self::$db->bind("change_user_password", ":password", $this->password);
        self::$db->execute("change_user_password");
    }
    
    /**
     * Saves the user's data to the database.
     * This is done automatically on object destruction, but can also be called
     * before that to ensure it happens.
     */
    public function save()
    {
        self::$db->bind("save_user", ":id", $this->id);
        self::$db->bind("save_user", ":name", $this->name);
        self::$db->bind("save_user", ":username", $this->username);
        self::$db->bind("save_user", ":admin", $this->admin);
        self::$db->bind("save_user", ":pop_id", $this->pop_id);
        self::$db->bind("save_user", ":avatar", $this->avatar);
        self::$db->execute("save_user");
    }
}