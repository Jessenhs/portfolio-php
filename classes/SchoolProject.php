<?php
require_once 'Project.php';

class SchoolProject extends Project {
    private $course;
    private $grade;
    private $semester;

    public function __construct($title = '', $description = '', $date = '', $image = 'default-project.jpg', $course = '', $grade = '', $semester = '') {
        parent::__construct($title, $description, 'school', $date, $image);
        $this->course = $course;
        $this->grade = $grade;
        $this->semester = $semester;
    }

    // Getters
    public function getCourse() {
        return $this->course;
    }

    public function getGrade() {
        return $this->grade;
    }

    public function getSemester() {
        return $this->semester;
    }

    // Setters
    public function setCourse($course) {
        $this->course = $course;
    }

    public function setGrade($grade) {
        $this->grade = $grade;
    }

    public function setSemester($semester) {
        $this->semester = $semester;
    }

    // Override save method to include school-specific fields
    public function save($conn) {
        // First save the base project data
        if (parent::save($conn)) {
            try {
                // Check if school details exist
                $stmt = $conn->prepare("SELECT * FROM school_details WHERE project_id = ?");
                $stmt->bind_param("i", $this->id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 1) {
                    // Update existing school details
                    $stmt = $conn->prepare("UPDATE school_details SET course = ?, grade = ?, semester = ? WHERE project_id = ?");
                    $stmt->bind_param("sssi", $this->course, $this->grade, $this->semester, $this->id);
                } else {
                    // Create new school details
                    $stmt = $conn->prepare("INSERT INTO school_details (project_id, course, grade, semester) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("isss", $this->id, $this->course, $this->grade, $this->semester);
                }
                
                return $stmt->execute();
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
                return false;
            }
        }
        
        return false;
    }

    // Override loadFromDatabase to include school-specific fields
    public function loadFromDatabase($conn, $id) {
        if (parent::loadFromDatabase($conn, $id)) {
            try {
                $stmt = $conn->prepare("SELECT * FROM school_details WHERE project_id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 1) {
                    $details = $result->fetch_assoc();
                    $this->course = $details['course'];
                    $this->grade = $details['grade'];
                    $this->semester = $details['semester'];
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