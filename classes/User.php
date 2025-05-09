<?php
require_once 'UserProfile.php';

class User {
    private $id;
    private $username;
    private $email;
    private $password;
    private $role;
    private $profile;

    public function __construct($username = '', $email = '', $password = '', $role = 'student') {
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->profile = new UserProfile();
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getRole() {
        return $this->role;
    }

    public function getProfile() {
        return $this->profile;
    }

    // Setters
    public function setId($id) {
        $this->id = $id;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setPassword($password) {
        // Hash password before storing
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    public function setRole($role) {
        $this->role = $role;
    }

    // Methods
    public function register($conn) {
        try {
            $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $this->username, $this->email, $hashedPassword, $this->role);
            
            if ($stmt->execute()) {
                $this->id = $conn->insert_id;
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function login($conn, $username, $password) {
        try {
            $stmt = $conn->prepare("SELECT id, username, email, password, role FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                if (password_verify($password, $user['password'])) {
                    $this->id = $user['id'];
                    $this->username = $user['username'];
                    $this->email = $user['email'];
                    $this->role = $user['role'];
                    
                    // Load user profile
                    $this->loadProfile($conn);
                    
                    return true;
                }
            }
            
            return false;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function loadProfile($conn) {
        $this->profile->loadFromDatabase($conn, $this->id);
    }

    public function getProjects($conn, $category = null) {
        $projects = [];
        
        try {
            $sql = "SELECT * FROM projects WHERE user_id = ?";
            $params = [$this->id];
            
            if ($category) {
                $sql .= " AND category = ?";
                $params[] = $category;
            }
            
            $stmt = $conn->prepare($sql);
            
            if (count($params) === 1) {
                $stmt->bind_param("i", $params[0]);
            } else {
                $stmt->bind_param("is", $params[0], $params[1]);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                if ($row['category'] === 'freelance') {
                    $project = new FreelanceProject();
                } elseif ($row['category'] === 'school') {
                    $project = new SchoolProject();
                } else {
                    $project = new Project();
                }
                
                $project->loadFromArray($row);
                $projects[] = $project;
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
        
        return $projects;
    }
}
?>