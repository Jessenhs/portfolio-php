<?php
require_once 'includes/header.php';
require_once 'classes/Project.php';
require_once 'classes/FreelanceProject.php';
require_once 'classes/SchoolProject.php';

// Get category filter
$category = isset($_GET['category']) ? $_GET['category'] : null;

// Get all projects
$projects = Project::getAllProjects($conn, $category);
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1>My Portfolio</h1>
        <p class="lead">Welcome to my portfolio website. Here you can find all my projects.</p>
    </div>
    <div class="col-md-4">
        <div class="d-flex justify-content-end align-items-center h-100">
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    Filter Projects
                </button>
                <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                    <li><a class="dropdown-item" href="index.php">All Projects</a></li>
                    <li><a class="dropdown-item" href="index.php?category=school">School Projects</a></li>
                    <li><a class="dropdown-item" href="index.php?category=freelance">Freelance Projects</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <?php if (count($projects) > 0): ?>
        <?php foreach ($projects as $project): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <img src="assets/img/<?php echo htmlspecialchars($project->getImage()); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($project->getTitle()); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($project->getTitle()); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars(substr($project->getDescription(), 0, 100)) . '...'; ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-<?php echo $project->getCategory() === 'school' ? 'success' : 'primary'; ?>">
                                <?php echo ucfirst(htmlspecialchars($project->getCategory())); ?>
                            </span>
                            <small class="text-muted"><?php echo htmlspecialchars($project->getDate()); ?></small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="project.php?id=<?php echo $project->getId(); ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-info">
                No projects found.
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>