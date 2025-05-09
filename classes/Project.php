<?php
class Project {
    protected $id;
    protected $userId;
    protected $title;
    protected $description;
    protected $category;
    protected $date;
    protected $image;
    protected $link;  
    protected $repository;  // New field

    public function __construct($title = '', $description = '', $category = '', $date = '', $image = 'default-project.jpg', $link = '', $repository = '') {
        $this->title = $title;
        $this->description = $description;
        $this->category = $category;
        $this->date = $date;
        $this->image = $image;
        $this->link = $link;
        $this->repository = $repository;
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getCategory() {
        return $this->category;
    }

    public function getDate() {
        return $this->date;
    }

    public function getImage() {
        return $this->image;
    }

    public function getLink() {
        return $this->link;
    }

    public function getRepository() {
        return $this->repository;
    }

    // Setters
    public function setId($id) {
        $this->id = $id;
    }

    public function setUserId($userId) {
        $this->userId = $userId;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setCategory($category) {
        $this->category = $category;
    }

    public function setDate($date) {
        $this->date = $date;
    }

    public function setImage($image) {
        $this->image = $image;
    }

    public function setLink($link) {
        $this->link = $link;
    }

    public function setRepository($repository) {
        $this->repository = $repository;
    }

    // Methods
    public function save($conn) {
        try {
            if ($this->id) {
                $stmt = $conn->prepare("UPDATE projects SET title = ?, description = ?, category = ?, date = ?, image = ?, link = ?, repository = ? WHERE id = ?");
                $stmt->bind_param("sssssssi", $this->title, $this->description, $this->category, $this->date, $this->image, $this->link, $this->repository, $this->id);
            } else {
                $stmt = $conn->prepare("INSERT INTO projects (user_id, title, description, category, date, image, link, repository) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("isssssss", $this->userId, $this->title, $this->description, $this->category, $this->date, $this->image, $this->link, $this->repository);
            }
            
            if ($stmt->execute()) {
                if (!$this->id) {
                    $this->id = $conn->insert_id;
                }
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function delete($conn) {
        try {
            $stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
            $stmt->bind_param("i", $this->id);
            return $stmt->execute();
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function loadFromDatabase($conn, $id) {
        try {
            $stmt = $conn->prepare("SELECT * FROM projects WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $project = $result->fetch_assoc();
                $this->loadFromArray($project);
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function loadFromArray($data) {
        $this->id = $data['id'];
        $this->userId = $data['user_id'];
        $this->title = $data['title'];
        $this->description = $data['description'];
        $this->category = $data['category'];
        $this->date = $data['date'];
        $this->image = $data['image'];
        $this->link = $data['link'];
        $this->repository = $data['repository'];
    }

    public static function getAllProjects($conn, $category = null) {
        $projects = [];
        
        try {
            $sql = "SELECT * FROM projects";
            $params = [];
            
            if ($category) {
                $sql .= " WHERE category = ?";
                $params[] = $category;
            }
            
            $stmt = $conn->prepare($sql);
            
            if (count($params) === 1) {
                $stmt->bind_param("s", $params[0]);
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