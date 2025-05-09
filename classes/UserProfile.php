<?php
class UserProfile {
    private $bio;
    private $profileImage;
    private $website;
    private $userId;

    public function __construct($bio = '', $profileImage = 'default.jpg', $website = '') {
        $this->bio = $bio;
        $this->profileImage = $profileImage;
        $this->website = $website;
    }

    // Getters
    public function getBio() {
        return $this->bio;
    }

    public function getProfileImage() {
        return $this->profileImage;
    }

    public function getWebsite() {
        return $this->website;
    }

    public function getUserId() {
        return $this->userId;
    }

    // Setters
    public function setBio($bio) {
        $this->bio = $bio;
    }

    public function setProfileImage($profileImage) {
        $this->profileImage = $profileImage;
    }

    public function setWebsite($website) {
        $this->website = $website;
    }

    public function setUserId($userId) {
        $this->userId = $userId;
    }

    // Methods
    public function save($conn, $userId) {
        try {
            // Check if profile exists
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                // Update existing profile
                $stmt = $conn->prepare("UPDATE users SET bio = ?, profileImage = ?, website = ? WHERE id = ?");
                $stmt->bind_param("sssi", $this->bio, $this->profileImage, $this->website, $userId);
                return $stmt->execute();
            }
            
            return false;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function loadFromDatabase($conn, $userId) {
        try {
            $stmt = $conn->prepare("SELECT bio, profileImage, website FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $profile = $result->fetch_assoc();
                $this->bio = $profile['bio'];
                $this->profileImage = $profile['profileImage'];
                $this->website = $profile['website'];
                $this->userId = $userId;
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
}
?>