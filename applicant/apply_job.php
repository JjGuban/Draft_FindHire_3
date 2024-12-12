<?php
session_start();
require '../core/dbConfig.php';
require '../core/models.php';

// Ensure the user is logged in and is an Applicant
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Applicant') {
    header("Location: ../index.php"); // Redirect to login page if not logged in
    exit;
}

$applicant_id = $_SESSION['user_id'];

// Fetch all job posts with application status for the logged-in applicant
$stmt = $pdo->prepare("
    SELECT jp.id AS job_id, jp.title, jp.description, jp.created_by, jp.created_at, 
    (SELECT a.application_status FROM applications a WHERE a.job_id = jp.id AND a.applicant_id = ?) AS application_status,
    u.username AS hr_name
    FROM job_posts jp
    INNER JOIN users u ON jp.created_by = u.id
    ORDER BY jp.created_at DESC
");
$stmt->execute([$applicant_id]);
$jobPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FindHire - Available Jobs</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        nav {
            background-color: #343a40;
            color: white;
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        nav a {
            color: white;
            text-decoration: none;
            margin-right: 15px;
        }
        nav a:hover {
            text-decoration: underline;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .job {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        .job h3 {
            margin: 0;
            font-size: 18px;
        }
        .job p {
            margin: 5px 0;
        }
        .job .status {
            font-weight: bold;
            color: #007bff;
        }
        .job button {
            background-color: #007bff;
            border: none;
            color: white;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 5px;
        }
        .job button:hover {
            background-color: #0056b3;
        }
        .disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <nav>
        <div>
            <strong>FindHire</strong>
        </div>
        <div>
            <a href="../dashboard.php">Dashboard</a>
            <a href="../core/handleForms.php?logout=1">Logout</a>
        </div>
    </nav>

    <div class="container">
        <h2>Available Job Posts</h2>
        <?php if (count($jobPosts) > 0): ?>
            <?php foreach ($jobPosts as $job): ?>
                <div class="job">
                    <h3><?= htmlspecialchars($job['title']) ?></h3>
                    <p><?= htmlspecialchars($job['description']) ?></p>
                    <p>Posted on: <?= htmlspecialchars($job['created_at']) ?></p>
                    <p><strong>HR:</strong> <?= htmlspecialchars($job['hr_name']) ?></p>
                    <p class="status">
                        Status: 
                        <?php 
                            if ($job['application_status'] === null) {
                                echo "Not Applied";
                            } else {
                                echo htmlspecialchars($job['application_status']);
                            }
                        ?>
                    </p>
                    <form action="upload_resume.php" method="POST" style="display: inline-block;">
                        <input type="hidden" name="job_id" value="<?= $job['job_id'] ?>">
                        <?php if ($job['application_status'] === null): ?>
                            <button type="submit">Apply for this Job</button>
                        <?php else: ?>
                            <button type="button" class="disabled">Already Applied</button>
                        <?php endif; ?>
                    </form>
                    <form action="follow_up.php" method="GET" style="display: inline-block;">
                        <input type="hidden" name="hr_id" value="<?= $job['created_by'] ?>">
                        <button type="submit">Message HR</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No job posts available at the moment.</p>
        <?php endif; ?>
    </div>
</body>
</html>
