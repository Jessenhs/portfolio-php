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
        $image = 'default-project.jpg';
        
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
                $targetPath = 'assets/img/' . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    $image = $filename;
                } else {
                    $error = 'Failed to upload image.';
                }
            }
        }
        
        if (empty($error)) {
            // Create project based on category
            if ($category === 'freelance') {
                $project = new FreelanceProject($title, $description, $date, $image);
            } elseif ($category === 'school') {
                $project = new SchoolProject($title, $description, $date, $image);
            } else {
                $project = new Project($title, $description, $category, $date, $image);
            }
            
            $project->setUserId($userId);
            $project->setLink($link);
            $project->setRepository($repository);
            
            if ($project->save($conn)) {
                $success = 'Project added successfully.';
                header('Location: dashboard.php?added=1');
                exit;
            } else {
                $error = 'Failed to add project.';
            }
        }
    }
}
?>

<div class="row mb-4">
    <div class="col-12">
        <h1>Add New Project</h1>
        <p class="lead">Create a new project to showcase your work.</p>
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
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Description *</label>
                <textarea class="form-control" id="description" name="description" rows="5" required></textarea>
            </div>
            
            <div class="mb-3">
                <label for="category" class="form-label">Category *</label>
                <select class="form-select" id="category" name="category" required>
                    <option value="">Select Category</option>
                    <option value="school">School Project</option>
                    <option value="freelance">Freelance Project</option>
                    <option value="personal">Personal Project</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control" id="date" name="date" value="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <div class="mb-3">
                <label for="image" class="form-label">Project Image</label>
                <input type="file" class="form-control" id="image" name="image">
                <div class="form-text">Upload an image for your project (JPG, PNG, or GIF, max 5MB).</div>
            </div>

            <!-- Add link and repository fields -->
            <div class="mb-3">
                <label for="link" class="form-label">Project Link</label>
                <input type="url" class="form-control" id="link" name="link" value="<?php echo isset($_POST['link']) ? htmlspecialchars($_POST['link']) : ''; ?>">
                <div class="form-text">Link to the live project (if available)</div>
            </div>

            <div class="mb-3">
                <label for="repository" class="form-label">Repository URL</label>
                <input type="url" class="form-control" id="repository" name="repository" value="<?php echo isset($_POST['repository']) ? htmlspecialchars($_POST['repository']) : ''; ?>">
                <div class="form-text">Link to the project's source code repository (GitHub, GitLab, etc.)</div>
            </div>

            <!-- Freelance Project Fields (initially hidden) -->
            <div id="freelance-fields" style="display: none;">
                <h4 class="mt-4 mb-3">Freelance Project Details</h4>
                
                <div class="mb-3">
                    <label for="client_name" class="form-label">Client Name</label>
                    <input type="text" class="form-control" id="client_name" name="client_name">
                </div>
                
                <div class="mb-3">
                    <label for="budget" class="form-label">Budget ($)</label>
                    <input type="number" class="form-control" id="budget" name="budget" min="0" step="0.01">
                </div>
                
                <div class="mb-3">
                    <label for="duration" class="form-label">Duration</label>
                    <input type="text" class="form-control" id="duration" name="duration" placeholder="e.g., 2 weeks, 3 months">
                </div>
            </div>
            
            <!-- School Project Fields (initially hidden) -->
            <div id="school-fields" style="display: none;">
                <h4 class="mt-4 mb-3">School Project Details</h4>
                
                <div class="mb-3">
                    <label for="course" class="form-label">Course</label>
                    <input type="text" class="form-control" id="course" name="course">
                </div>
                
                <div class="mb-3">
                    <label for="grade" class="form-label">Grade</label>
                    <input type="text" class="form-control" id="grade" name="grade">
                </div>
                
                <div class="mb-3">
                    <label for="semester" class="form-label">Semester</label>
                    <input type="text" class="form-control" id="semester" name="semester">
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Add Project</button>
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
