<?php
session_start();
require '../core/dbConfig.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'HR') {
    header("Location: ../dashboard.php");
    exit;
}


$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $created_by = $_SESSION['user_id'];

    if (empty($title) || empty($description)) {
        $errorMessage = 'Title and description cannot be empty.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO job_posts (title, description, created_by) VALUES (?, ?, ?)");
            $stmt->execute([$title, $description, $created_by]);
            header('Location: job_posts.php');
            exit();
        } catch (PDOException $e) {
            $errorMessage = 'Error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Job Post</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <div class="container">
        <h1>Add a New Job Post</h1>
        <a href="job_posts.php" class="btn">Back to Job Posts</a>
        <a href="../core/handleForms.php?logout=1" class="btn btn-danger">Logout</a>

        <?php if (!empty($errorMessage)): ?>
            <div class="error">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <form action="add_job.php" method="POST">
            <div class="form-group">
                <label for="title">Job Title:</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="description">Job Description:</label>
                <textarea id="description" name="description" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Add Job</button>
        </form>
    </div>
</body>
</html>
