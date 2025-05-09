<?php
require_once 'includes/header.php';
require_once 'classes/Project.php';
require_once 'classes/FreelanceProject.php';
require_once 'classes/SchoolProject.php';

// Check if project ID is provided
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$projectId = $_GET['id'];

// Load project
$project = null;
$projectData = null;

// First, check the category to determine which class to use
$stmt = $conn->prepare("SELECT category FROM projects WHERE id = ?");
$stmt->bind_param("i", $projectId);
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
    header('Location: index.php');
    exit;
}

// Get user info
$userId = $project->getUserId(); // Store method result in a variable first
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $userId); // Pass the variable instead of the method call
$stmt->execute();
$userResult = $stmt->get_result();
$userData = $userResult->fetch_assoc();
$username = $userData['username'] ?? 'Unknown';
?>

<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="index.php?category=<?php echo $project->getCategory(); ?>"><?php echo ucfirst($project->getCategory()); ?> Projects</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($project->getTitle()); ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <h1><?php echo htmlspecialchars($project->getTitle()); ?></h1>
        <div class="d-flex mb-3">
            <span class="badge bg-<?php echo $project->getCategory() === 'school' ? 'success' : 'primary'; ?> me-2">
                <?php echo ucfirst(htmlspecialchars($project->getCategory())); ?>
            </span>
            <span class="text-muted">Created by <?php echo htmlspecialchars($username); ?> on <?php echo htmlspecialchars($project->getDate()); ?></span>
        </div>
        <img src="assets/img/<?php echo htmlspecialchars($project->getImage()); ?>" class="img-fluid rounded mb-4" alt="<?php echo htmlspecialchars($project->getTitle()); ?>">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Description</h5>
                <p class="card-text"><?php echo nl2br(htmlspecialchars($project->getDescription())); ?></p>
            </div>
        </div>
        
        <?php if ($project instanceof FreelanceProject): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Freelance Project Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <p><strong>Client:</strong> <?php echo htmlspecialchars($project->getClientName()); ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Budget:</strong> $<?php echo htmlspecialchars($project->getBudget()); ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Duration:</strong> <?php echo htmlspecialchars($project->getDuration()); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php elseif ($project instanceof SchoolProject): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">School Project Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <p><strong>Course:</strong> <?php echo htmlspecialchars($project->getCourse()); ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Grade:</strong> <?php echo htmlspecialchars($project->getGrade()); ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Semester:</strong> <?php echo htmlspecialchars($project->getSemester()); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="index.php" class="btn btn-primary">Back to Projects</a>
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] === $project->getUserId()): ?>
                    <a href="edit-project.php?id=<?php echo $project->getId(); ?>" class="btn btn-warning">Edit Project</a>
                    <a href="dashboard.php?action=delete&id=<?php echo $project->getId(); ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this project?')">Delete Project</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Related Projects</h5>
            </div>
            <div class="card-body">
                <?php
                // Get related projects (same category, different ID)
                $category = $project->getCategory();
                $id = $projectId;
                $stmt = $conn->prepare("SELECT id, title, image FROM projects WHERE category = ? AND id != ? LIMIT 3");
                $stmt->bind_param("si", $category, $id);
                $stmt->execute();
                $relatedResult = $stmt->get_result();
                
                if ($relatedResult->num_rows > 0):
                ?>
                <div class="list-group">
                    <?php while ($related = $relatedResult->fetch_assoc()): ?>
                    <a href="project.php?id=<?php echo $related['id']; ?>" class="list-group-item list-group-item-action d-flex align-items-center">
                        <img src="assets/img/<?php echo htmlspecialchars($related['image']); ?>" alt="<?php echo htmlspecialchars($related['title']); ?>" class="me-3" style="width: 50px; height: 50px; object-fit: cover;">
                        <span><?php echo htmlspecialchars($related['title']); ?></span>
                    </a>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <p class="text-muted">No related projects found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>