<?php
require_once 'Project.php';

class FreelanceProject extends Project {
    private $clientName;
    private $budget;
    private $duration;

    public function __construct($title = '', $description = '', $date = '', $image = 'default-project.jpg', $clientName = '', $budget = 0, $duration = '') {
        parent::__construct($title, $description, 'freelance', $date, $image);
        $this->clientName = $clientName;
        $this->budget = $budget;
        $this->duration = $duration;
    }

    // Getters
    public function getClientName() {
        return $this->clientName;
    }

    public function getBudget() {
        return $this->budget;
    }

    public function getDuration() {
        return $this->duration;
    }

    // Setters
    public function setClientName($clientName) {
        $this->clientName = $clientName;
    }

    public function setBudget($budget) {
        $this->budget = $budget;
    }

    public function setDuration($duration) {
        $this->duration = $duration;
    }

    // Override save method to include freelance-specific fields
    public function save($conn) {
        // First save the base project data
        if (parent::save($conn)) {
            try {
                // Check if freelance details exist
                $stmt = $conn->prepare("SELECT * FROM freelance_details WHERE project_id = ?");
                $stmt->bind_param("i", $this->id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 1) {
                    // Update existing freelance details
                    $stmt = $conn->prepare("UPDATE freelance_details SET client_name = ?, budget = ?, duration = ? WHERE project_id = ?");
                    $stmt->bind_param("sdsi", $this->clientName, $this->budget, $this->duration, $this->id);
                } else {
                    // Create new freelance details
                    $stmt = $conn->prepare("INSERT INTO freelance_details (project_id, client_name, budget, duration) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("isds", $this->id, $this->clientName, $this->budget, $this->duration);
                }
                
                return $stmt->execute();
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
                return false;
            }
        }
        
        return false;
    }

    // Override loadFromDatabase to include freelance-specific fields
    public function loadFromDatabase($conn, $id) {
        if (parent::loadFromDatabase($conn, $id)) {
            try {
                $stmt = $conn->prepare("SELECT * FROM freelance_details WHERE project_id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 1) {
                    $details = $result->fetch_assoc();
                    $this->clientName = $details['client_name'];
                    $this->budget = $details['budget'];
                    $this->duration = $details['duration'];
                    return true;
                }
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
            }
        }
        
        return false;
    }
}
?>