<?php
session_start();
require '../core/dbConfig.php';

if ($_SESSION['role'] !== 'HR') {
    header("Location: ../dashboard.php");
    exit;
}

if (!isset($_GET['job_id'])) {
    echo "No job selected.";
    exit;
}

$job_id = intval($_GET['job_id']);

// Fetch the job details
$stmt = $pdo->prepare("SELECT * FROM job_posts WHERE id = ?");
$stmt->execute([$job_id]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    echo "Job not found.";
    exit;
}

// Fetch the applications for the job
$stmt = $pdo->prepare("
    SELECT a.id AS application_id, u.username, a.resume_path, a.application_status, a.submitted_at
    FROM applications a
    JOIN users u ON a.applicant_id = u.id
    WHERE a.job_id = ?
");
$stmt->execute([$job_id]);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Applicants</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        .actions button {
            margin-right: 5px;
            padding: 5px 10px;
            cursor: pointer;
            border: none;
            border-radius: 3px;
        }
        .accept {
            background-color: #28a745;
            color: white;
        }
        .reject {
            background-color: #dc3545;
            color: white;
        }
        .back {
            text-decoration: none;
            padding: 10px 15px;
            background-color: #6c757d;
            color: white;
            border-radius: 5px;
            display: inline-block;
            margin-bottom: 20px;
        }
        .back:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Applicants for Job: <?= htmlspecialchars($job['title']) ?></h2>
        <a href="job_posts.php" class="back">Back to Job Posts</a>
        <?php if ($applications): ?>
            <table>
                <thead>
                    <tr>
                        <th>Applicant</th>
                        <th>Resume</th>
                        <th>Status</th>
                        <th>Applied On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $app): ?>
                        <tr>
                            <td><?= htmlspecialchars($app['username']) ?></td>
                            <td><a href="../uploads/<?= htmlspecialchars($app['resume_path']) ?>" target="_blank">View Resume</a></td>
                            <td><?= htmlspecialchars($app['application_status']) ?></td>
                            <td><?= htmlspecialchars($app['submitted_at']) ?></td>
                            <td class="actions">
                                <?php if ($app['application_status'] === 'Pending'): ?>
                                    <a href="../core/handleForms.php?action=accept&application_id=<?= $app['application_id'] ?>">
                                        <button class="accept">Accept</button>
                                    </a>
                                    <a href="../core/handleForms.php?action=reject&application_id=<?= $app['application_id'] ?>">
                                        <button class="reject">Reject</button>
                                    </a>
                                <?php else: ?>
                                    <em><?= htmlspecialchars($app['application_status']) ?></em>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No applications for this job yet.</p>
        <?php endif; ?>
    </div>
</body>
</html>
