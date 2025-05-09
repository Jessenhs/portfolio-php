<?php
require_once 'includes/header.php';
require_once 'classes/Project.php';
require_once 'classes/FreelanceProject.php';
require_once 'classes/SchoolProject.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Check if project ID is provided
if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$projectId = $_GET['id'];

// Load project
$project = null;
$projectData = null;

// First, check the category to determine which class to use
$stmt = $conn->prepare("SELECT category FROM projects WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $projectId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $projectData = $result->fetch_assoc();
    
    if ($projectData['category'] === 'freelance') {
        $project = new FreelanceProject();
    } elseif ($projectData['category'] === 'school') {
        $project = new SchoolProject();
    } else {
        $project = new Project();
    }
    
    $project->loadFromDatabase($conn, $projectId);
} else {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $category = $_POST['category'] ?? '';
    $date = $_POST['date'] ?? date('Y-m-d');
    $link = $_POST['link'] ?? '';
    $repository = $_POST['repository'] ?? '';
    
    // Validate inputs
    if (empty($title) || empty($description) || empty($category)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Handle image upload
        $image = $project->getImage();
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($_FILES['image']['type'], $allowedTypes)) {
                $error = 'Only JPG, PNG, and GIF images are allowed.';
            } elseif ($_FILES['image']['size'] > $maxSize) {
                $error = 'Image size should be less than 5MB.';
            } else {
                $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $filename = 'project_' . time() . '.' . $extension;
                $uploadDir = 'assets/img/';
                $targetPath = $uploadDir . $filename;
                
                // Create directory if it doesn't exist
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $image = $filename;
                } else {
                    $error = 'Failed to upload image. Please check directory permissions.';
                }
            }
        }
        
        if (empty($error)) {
            // Update project based on category
            $project->setTitle($title);
            $project->setDescription($description);
            $project->setCategory($category);
            $project->setDate($date);
            $project->setImage($image);
            $project->setLink($link);
            $project->setRepository($repository);
            
            if ($category === 'freelance' && $project instanceof FreelanceProject) {
                $clientName = $_POST['client_name'] ?? '';
                $budget = $_POST['budget'] ?? 0;
                $duration = $_POST['duration'] ?? '';
                
                $project->setClientName($clientName);
                $project->setBudget($budget);
                $project->setDuration($duration);
            } elseif ($category === 'school' && $project instanceof SchoolProject) {
                $course = $_POST['course'] ?? '';
                $grade = $_POST['grade'] ?? '';
                $semester = $_POST['semester'] ?? '';
                
                $project->setCourse($course);
                $project->setGrade($grade);
                $project->setSemester($semester);
            }
            
            if ($project->save($conn)) {
                $success = 'Project updated successfully.';
            } else {
                $error = 'Failed to update project.';
            }
        }
    }
}
?>

<div class="row mb-4">
    <div class="col-12">
        <h1>Edit Project</h1>
        <p class="lead">Update your project information.</p>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="title" class="form-label">Title *</label>
                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($project->getTitle()); ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Description *</label>
                <textarea class="form-control" id="description" name="description" rows="5" required><?php echo htmlspecialchars($project->getDescription()); ?></textarea>
            </div>
            
            <div class="mb-3">
                <label for="category" class="form-label">Category *</label>
                <select class="form-select" id="category" name="category" required>
                    <option value="">Select Category</option>
                    <option value="school" <?php echo $project->getCategory() === 'school' ? 'selected' : ''; ?>>School Project</option>
                    <option value="freelance" <?php echo $project->getCategory() === 'freelance' ? 'selected' : ''; ?>>Freelance Project</option>
                    <option value="personal" <?php echo $project->getCategory() === 'personal' ? 'selected' : ''; ?>>Personal Project</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control" id="date" name="date" value="<?php echo htmlspecialchars($project->getDate()); ?>">
            </div>
            
            <div class="mb-3">
                <label for="image" class="form-label">Project Image</label>
                <div class="mb-2">
                    <img src="assets/img/<?php echo htmlspecialchars($project->getImage()); ?>" alt="Project Image" style="max-width: 200px; max-height: 200px;">
                </div>
                <input type="file" class="form-control" id="image" name="image">
                <div class="form-text">Upload a new image for your project (JPG, PNG, or GIF, max 5MB).</div>
            </div>
            
            <!-- Freelance Project Fields -->
            <div id="freelance-fields" style="display: <?php echo $project instanceof FreelanceProject ? 'block' : 'none'; ?>;">
                <h4 class="mt-4 mb-3">Freelance Project Details</h4>
                
                <div class="mb-3">
                    <label for="client_name" class="form-label">Client Name</label>
                    <input type="text" class="form-control" id="client_name" name="client_name" value="<?php echo $project instanceof FreelanceProject ? htmlspecialchars($project->getClientName()) : ''; ?>">
                </div>
                
                <div class="mb-3">
                    <label for="budget" class="form-label">Budget ($)</label>
                    <input type="number" class="form-control" id="budget" name="budget" min="0" step="0.01" value="<?php echo $project instanceof FreelanceProject ? htmlspecialchars($project->getBudget()) : '0'; ?>">
                </div>
                
                <div class="mb-3">
                    <label for="duration" class="form-label">Duration</label>
                    <input type="text" class="form-control" id="duration" name="duration" placeholder="e.g., 2 weeks, 3 months" value="<?php echo $project instanceof FreelanceProject ? htmlspecialchars($project->getDuration()) : ''; ?>">
                </div>
            </div>
            
            <!-- School Project Fields -->
            <div id="school-fields" style="display: <?php echo $project instanceof SchoolProject ? 'block' : 'none'; ?>;">
                <h4 class="mt-4 mb-3">School Project Details</h4>
                
                <div class="mb-3">
                    <label for="course" class="form-label">Course</label>
                    <input type="text" class="form-control" id="course" name="course" value="<?php echo $project instanceof SchoolProject ? htmlspecialchars($project->getCourse()) : ''; ?>">
                </div>
                
                <div class="mb-3">
                    <label for="grade" class="form-label">Grade</label>
                    <input type="text" class="form-control" id="grade" name="grade" value="<?php echo $project instanceof SchoolProject ? htmlspecialchars($project->getGrade()) : ''; ?>">
                </div>
                
                <div class="mb-3">
                    <label for="semester" class="form-label">Semester</label>
                    <input type="text" class="form-control" id="semester" name="semester" value="<?php echo $project instanceof SchoolProject ? htmlspecialchars($project->getSemester()) : ''; ?>">
                </div>
            </div>
            
            <div class="mb-3">
                <label for="link" class="form-label">Project Link</label>
                <input type="url" class="form-control" id="link" name="link" value="<?php echo htmlspecialchars($project->getLink()); ?>">
                <div class="form-text">Link to the live project (if available)</div>
            </div>

            <div class="mb-3">
                <label for="repository" class="form-label">Repository URL</label>
                <input type="url" class="form-control" id="repository" name="repository" value="<?php echo htmlspecialchars($project->getRepository()); ?>">
                <div class="form-text">Link to the project's source code repository (GitHub, GitLab, etc.)</div>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Update Project</button>
                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
    // Show/hide category-specific fields based on selection
    document.getElementById('category').addEventListener('change', function() {
        const category = this.value;
        const freelanceFields = document.getElementById('freelance-fields');
        const schoolFields = document.getElementById('school-fields');
        
        freelanceFields.style.display = category === 'freelance' ? 'block' : 'none';
        schoolFields.style.display = category === 'school' ? 'block' : 'none';
    });
</script>

<?php require_once 'includes/footer.php'; ?>