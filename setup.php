<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'portfolio';

// Create connection
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS $database";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select database
$conn->select_db($database);

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'student',
    bio TEXT,
    profileImage VARCHAR(255) DEFAULT 'default.jpg',
    website VARCHAR(255)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'users' created successfully<br>";
} else {
    echo "Error creating table 'users': " . $conn->error . "<br>";
}

// Create projects table
$sql = "CREATE TABLE IF NOT EXISTS projects (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(50) NOT NULL,
    date DATE NOT NULL,
    image VARCHAR(255) DEFAULT 'default-project.jpg',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'projects' created successfully<br>";
} else {
    echo "Error creating table 'projects': " . $conn->error . "<br>";
}

// Create freelance_details table
$sql = "CREATE TABLE IF NOT EXISTS freelance_details (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    project_id INT(11) NOT NULL,
    client_name VARCHAR(100),
    budget DECIMAL(10,2) DEFAULT 0,
    duration VARCHAR(50),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'freelance_details' created successfully<br>";
} else {
    echo "Error creating table 'freelance_details': " . $conn->error . "<br>";
}

// Create school_details table
$sql = "CREATE TABLE IF NOT EXISTS school_details (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    project_id INT(11) NOT NULL,
    course VARCHAR(100),
    grade VARCHAR(20),
    semester VARCHAR(50),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'school_details' created successfully<br>";
} else {
    echo "Error creating table 'school_details': " . $conn->error . "<br>";
}

// Create default images directory
if (!file_exists('assets/img')) {
    mkdir('assets/img', 0777, true);
    echo "Created 'assets/img' directory<br>";
}

// Close connection
$conn->close();

echo "<br>Setup completed successfully!";
?>