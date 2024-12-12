<?php
// edit_job.php
session_start();
require '../core/dbConfig.php';

if ($_SESSION['role'] !== 'HR') {
    header("Location: ../dashboard.php");
    exit;
}

// Handle editing a job post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_job'])) {
    $jobId = intval($_POST['job_id']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    if (!empty($title) && !empty($description)) {
        $stmt = $pdo->prepare("UPDATE job_posts SET title = ?, description = ? WHERE id = ?");
        if ($stmt->execute([$title, $description, $jobId])) {
            header("Location: job_posts.php?success=edit");
            exit;
        } else {
            $error = "Failed to update the job post.";
        }
    } else {
        $error = "All fields are required.";
    }
}

// Fetch job post details for editing
$job = null;
if (isset($_GET['job_id'])) {
    $jobId = intval($_GET['job_id']);
    $stmt = $pdo->prepare("SELECT * FROM job_posts WHERE id = ?");
    $stmt->execute([$jobId]);
    $job = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$job) {
        header("Location: job_posts.php?error=notfound");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Job Post</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Job Post</h2>
        <?php if (isset($error)) echo "<p class='error'>" . htmlspecialchars($error) . "</p>"; ?>
        <?php if (isset($job)): ?>
            <form method="POST">
                <input type="hidden" name="job_id" value="<?= htmlspecialchars($job['id']) ?>">
                <div class="form-group">
                    <label for="title">Job Title</label>
                    <input type="text" id="title" name="title" value="<?= htmlspecialchars($job['title']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Job Description</label>
                    <textarea id="description" name="description" rows="5" required><?= htmlspecialchars($job['description']) ?></textarea>
                </div>
                <button type="submit" name="edit_job">Save Changes</button>
            </form>
        <?php endif; ?>
        <a href="job_posts.php">Back to Job Posts</a>
    </div>
</body>
</html>