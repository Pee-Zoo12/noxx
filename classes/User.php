<?php
class User {
    protected $userID;
    protected $username;
    protected $email;
    protected $password;
    protected $birthday; 
    protected $sex;
    protected $address;
    protected $phone;
    
    // Constructor
    public function __construct($userData = null) {
        if ($userData) {
            $this->userID = $userData['userID'] ?? null;
            $this->username = $userData['username'] ?? '';
            $this->email = $userData['email'] ?? '';
            $this->password = $userData['password'] ?? '';
            $this->birthday = $userData['birthday'] ?? '';
            $this->sex = $userData['sex'] ?? '';
            $this->address = $userData['address'] ?? '';
            $this->phone = $userData['phone'] ?? '';
        }
    }
    
    // Register a new user
    public function register() {
        // Validate required fields
        if (empty($this->username) || empty($this->email) || empty($this->password)) {
            return false;
        }
        
        // Check if email already exists
        $users = readJsonFile(USERS_FILE);
        foreach ($users as $userType => $userList) {
            foreach ($userList as $user) {
                if ($user['email'] === $this->email) {
                    return false;
                }
            }
        }
        
        // Generate user ID
        $this->userID = 'USR' . time();
        
        // Hash password
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        
        // Save user data
        $userData = $this->toArray();
        $users['customers'][] = $userData;
        
        return writeJsonFile(USERS_FILE, $users);
    }
    
    // Login user
    public function login($email, $password) {
        $users = readJsonFile(USERS_FILE);
        
        foreach ($users as $userType => $userList) {
            foreach ($userList as $user) {
                if ($user['email'] === $email && password_verify($password, $user['password'])) {
                    // Set session data
                    $_SESSION['user_id'] = $user['userID'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['is_logged_in'] = true;
                    return true;
                }
            }
        }
        return false;
    }
    
    // Logout user
    public function logout() {
        session_unset();
        session_destroy();
        return true;
    }
    
    // Update user profile
    public function updateProfile() {
        $users = readJsonFile(USERS_FILE);
        
        foreach ($users as $userType => &$userList) {
            foreach ($userList as $key => $user) {
                if ($user['userID'] === $this->userID) {
                    // Don't update password if it's empty
                    if (empty($this->password)) {
                        $this->password = $user['password'];
                    } else {
                        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
                    }
                    
                    // Update user data
                    $userList[$key] = $this->toArray();
                    return writeJsonFile(USERS_FILE, $users);
                }
            }
        }
        
        return false;
    }
    
    
    public function toArray() {
        return [
            'userID' => $this->userID,
            'username' => $this->username,
            'email' => $this->email,
            'password' => $this->password,
            'birthday' => $this->birthday,
            'sex' => $this->sex,
            'address' => $this->address,
            'phone' => $this->phone
        ];
    }
    
    // Getters and setters
    public function getUserID() {
        return $this->userID;
    }
    
    public function setUserID($userID) {
        $this->userID = $userID;
    }
    
    public function getUsername() {
        return $this->username;
    }
    
    public function setUsername($username) {
        $this->username = $username;
    }
    
    public function getEmail() {
        return $this->email;
    }
    
    public function setEmail($email) {
        $this->email = $email;
    }
    
    public function getPassword() {
        return $this->password;
    }
    
    public function setPassword($password) {
        $this->password = $password;
    }
    
    public function getBirthday() {
        return $this->birthday;
    }
    
    public function setBirthday($birthday) {
        $this->birthday = $birthday;
    }
    
    public function getSex() {
        return $this->sex;
    }
    
    public function setSex($sex) {
        $this->sex = $sex;
    }
    
    public function getAddress() {
        return $this->address;
    }
    
    public function setAddress($address) {
        $this->address = $address;
    }

    public function getPhone() {
        return $this->phone;
    }
    
    public function setPhone($phone) {
        $this->phone = $phone;
    }

    // Get user by ID
    public function getById($userId) {
        $users = readJsonFile(USERS_FILE);
        
        foreach ($users as $userType => $userList) {
            foreach ($userList as $user) {
                if ($user['userID'] === $userId) {
                    return $user;
                }
            }
        }
        
        return null;
    }

    // Get user by email
    public static function getByEmail($email) {
        $users = readJsonFile(USERS_FILE);
        
        foreach ($users as $userType => $userList) {
            foreach ($userList as $user) {
                if ($user['email'] === $email) {
                    return new self($user);
                }
            }
        }
        
        return null;
    }
}
?>