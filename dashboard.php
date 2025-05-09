<?php
require_once 'includes/header.php';
require_once 'classes/User.php';
require_once 'classes/Project.php';
require_once 'classes/FreelanceProject.php';
require_once 'classes/SchoolProject.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$user = new User();
$user->setId($userId);

// Get user's projects
$projects = $user->getProjects($conn);

// Process delete project
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $projectId = $_GET['id'];
    $project = new Project();
    
    if ($project->loadFromDatabase($conn, $projectId) && $project->getUserId() === $userId) {
        if ($project->delete($conn)) {
            header('Location: dashboard.php?deleted=1');
            exit;
        }
    }
}
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1>Dashboard</h1>
        <p class="lead">Manage your projects and profile.</p>
    </div>
    <div class="col-md-4">
        <div class="d-flex justify-content-end align-items-center h-100">
            <a href="add-project.php" class="btn btn-primary">Add New Project</a>
        </div>
    </div>
</div>

<?php if (isset($_GET['deleted']) && $_GET['deleted'] === '1'): ?>
    <div class="alert alert-success">Project deleted successfully.</div>
<?php endif; ?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3>My Projects</h3>
            </div>
            <div class="card-body">
                <?php if (count($projects) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($projects as $project): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($project->getTitle()); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $project->getCategory() === 'school' ? 'success' : 'primary'; ?>">
                                                <?php echo ucfirst(htmlspecialchars($project->getCategory())); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($project->getDate()); ?></td>
                                        <td>
                                            <a href="project.php?id=<?php echo $project->getId(); ?>" class="btn btn-sm btn-info">View</a>
                                            <a href="edit-project.php?id=<?php echo $project->getId(); ?>" class="btn btn-sm btn-warning">Edit</a>
                                            <a href="dashboard.php?action=delete&id=<?php echo $project->getId(); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this project?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        You don't have any projects yet. <a href="add-project.php">Add your first project</a>.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3>Quick Links</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <h5 class="card-title">Profile</h5>
                                <p class="card-text">Update your profile information and settings.</p>
                                <a href="profile.php" class="btn btn-primary">Edit Profile</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <h5 class="card-title">Add Project</h5>
                                <p class="card-text">Create a new project to showcase your work.</p>
                                <a href="add-project.php" class="btn btn-primary">Add Project</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <h5 class="card-title">View Portfolio</h5>
                                <p class="card-text">See how your portfolio looks to visitors.</p>
                                <a href="index.php" class="btn btn-primary">View Portfolio</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>