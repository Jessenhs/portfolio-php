<?php
require_once 'includes/header.php';
require_once 'classes/User.php';
require_once 'classes/UserProfile.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$user = new User();
$user->setId($userId);

// Load user data
$stmt = $conn->prepare("SELECT username, email, role FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();

$user->setUsername($userData['username']);
$user->setEmail($userData['email']);
$user->setRole($userData['role']);
$user->loadProfile($conn);

$profile = $user->getProfile();

$error = '';
$success = '';

// Process profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update profile information
    $bio = $_POST['bio'] ?? '';
    $website = $_POST['website'] ?? '';
    
    // Handle profile image upload
    $profileImage = $profile->getProfileImage();
    
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($_FILES['profile_image']['type'], $allowedTypes)) {
            $error = 'Only JPG, PNG, and GIF images are allowed.';
        } elseif ($_FILES['profile_image']['size'] > $maxSize) {
            $error = 'Image size should be less than 2MB.';
        } else {
            $extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $userId . '_' . time() . '.' . $extension;
            $targetPath = 'assets/img/' . $filename;
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetPath)) {
                $profileImage = $filename;
            } else {
                $error = 'Failed to upload image.';
            }
        }
    }
    
    if (empty($error)) {
        $profile->setBio($bio);
        $profile->setWebsite($website);
        $profile->setProfileImage($profileImage);
        
        if ($profile->save($conn, $userId)) {
            $success = 'Profile updated successfully.';
        } else {
            $error = 'Failed to update profile.';
        }
    }
}
?>

<div class="row mb-4">
    <div class="col-12">
        <h1>My Profile</h1>
        <p class="lead">Update your profile information.</p>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <img src="assets/img/<?php echo htmlspecialchars($profile->getProfileImage()); ?>" alt="Profile Image" class="profile-image mb-3">
                <h3><?php echo htmlspecialchars($user->getUsername()); ?></h3>
                <p class="text-muted"><?php echo ucfirst(htmlspecialchars($user->getRole())); ?></p>
                <p><?php echo htmlspecialchars($user->getEmail()); ?></p>
                <?php if ($profile->getWebsite()): ?>
                    <p><a href="<?php echo htmlspecialchars($profile->getWebsite()); ?>" target="_blank"><?php echo htmlspecialchars($profile->getWebsite()); ?></a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3>Edit Profile</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user->getUsername()); ?>" disabled>
                        <div class="form-text">Username cannot be changed.</div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($user->getEmail()); ?>" disabled>
                        <div class="form-text">Email cannot be changed.</div>
                    </div>
                    <div class="mb-3">
                        <label for="bio" class="form-label">Bio</label>
                        <textarea class="form-control" id="bio" name="bio" rows="4"><?php echo htmlspecialchars($profile->getBio()); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="website" class="form-label">Website</label>
                        <input type="url" class="form-control" id="website" name="website" value="<?php echo htmlspecialchars($profile->getWebsite()); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="profile_image" class="form-label">Profile Image</label>
                        <input type="file" class="form-control" id="profile_image" name="profile_image">
                        <div class="form-text">Upload a new profile image (JPG, PNG, or GIF, max 2MB).</div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>