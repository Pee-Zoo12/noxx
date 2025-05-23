<?php
require_once 'includes/init.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

// Get user data
$userData = $user->getById($_SESSION['user_id']);
if (!$userData) {
    $error = "User data not found.";
    $userData = [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updatedUser = new User([
        'userID' => $_SESSION['user_id'],
        'username' => $_POST['username'],
        'email' => $_POST['email'],
        'password' => $_POST['password'] ?: ($userData['password'] ?? ''),
        'birthday' => $_POST['birthday'],
        'sex' => $_POST['sex'],
        'address' => $_POST['address'],
        'phone' => $_POST['phone']
    ]);

    if ($updatedUser->updateProfile()) {
        $success = 'Profile updated successfully!';
        $userData = $updatedUser->getById($_SESSION['user_id']) ?? [];
    } else {
        $error = 'Failed to update profile. Please try again.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - NOX Clothing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <?php include 'includes/templates/header.php'; ?>

    <main class="container py-5">
        <section class="profile-section">
            <h2 class="mb-4">My Profile</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" class="profile-form">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username"
                            value="<?php echo isset($userData['username']) ? htmlspecialchars($userData['username']) : ''; ?>"
                            required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email"
                            value="<?php echo isset($userData['email']) ? htmlspecialchars($userData['email']) : ''; ?>"
                            required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">New Password (leave blank to keep current)</label>
                        <input type="password" class="form-control" id="password" name="password">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="birthday" class="form-label">Birthday</label>
                        <input type="date" class="form-control" id="birthday" name="birthday"
                            value="<?php echo isset($userData['birthday']) ? htmlspecialchars($userData['birthday']) : ''; ?>"
                            required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="sex" class="form-label">Gender</label>
                        <select class="form-select" id="sex" name="sex" required>
                            <option value="">Select Gender</option>
                            <option value="male" <?php echo (isset($userData['sex']) && $userData['sex'] === 'male') ? 'selected' : ''; ?>>Male</option>
                            <option value="female" <?php echo (isset($userData['sex']) && $userData['sex'] === 'female') ? 'selected' : ''; ?>>Female</option>
                            <option value="other" <?php echo (isset($userData['sex']) && $userData['sex'] === 'other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone"
                            value="<?php echo isset($userData['phone']) ? htmlspecialchars($userData['phone']) : ''; ?>"
                            required>
                    </div>

                    <div class="col-12 mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"
                            required><?php echo isset($userData['address']) ? htmlspecialchars($userData['address']) : ''; ?></textarea>
                    </div>
                </div>

                <div class="d-flex gap-3 mt-4">
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                    <a href="logout.php" class="btn btn-outline-danger">Logout</a>
                </div>
            </form>
        </section>
    </main>

    <?php include 'includes/templates/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>