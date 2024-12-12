<?php
session_start();
require '../core/dbConfig.php';
require '../core/models.php';

if ($_SESSION['role'] !== 'HR') {
    header("Location: ../dashboard.php");
    exit;
}

$hrId = $_SESSION['user_id'];
$applicantId = $_GET['applicant_id'] ?? null;

// Fetch job posts
$stmt = $pdo->query("SELECT * FROM job_posts WHERE created_by = $hrId ORDER BY created_at DESC");
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch applicants who messaged the HR
$stmt = $pdo->prepare(
    "SELECT DISTINCT sender.id, sender.username 
    FROM messages 
    INNER JOIN users AS sender ON messages.sender_id = sender.id
    WHERE messages.receiver_id = ?"
);
$stmt->execute([$hrId]);
$applicants = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch messages with a specific applicant
$messages = $applicantId ? getMessages($hrId, $applicantId) : [];

// Handle reply
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiverId = $_POST['applicant_id'];
    $content = trim($_POST['content']);
    if (!empty($content)) {
        sendMessage($hrId, $receiverId, $content);
        $success = "Reply sent successfully.";
        $messages = getMessages($hrId, $receiverId); // Refresh messages
    } else {
        $error = "Reply cannot be empty.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Job Posts</title>
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
        .container h2 {
            text-align: center;
        }
        .job-post {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f8f9fa;
        }
        .job-post h3 {
            margin: 0;
        }
        .job-post p {
            margin: 5px 0;
        }
        .job-post .actions {
            margin-top: 10px;
            text-align: right;
        }
        .job-post button {
            background-color: #007bff;
            border: none;
            color: white;
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
            margin-right: 5px;
        }
        .job-post button:hover {
            background-color: #0056b3;
        }
        .add-job {
            text-align: right;
            margin-bottom: 20px;
        }
        .add-job a {
            text-decoration: none;
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
        }
        .add-job a:hover {
            background-color: #218838;
        }
        .logout {
            text-align: right;
        }
        .logout a {
            text-decoration: none;
            color: #dc3545;
            font-weight: bold;
        }
        .logout a:hover {
            text-decoration: underline;
        }
        .messages {
            margin-top: 40px;
        }
        .messages .message-list {
            border: 1px solid #ddd;
            padding: 15px;
            max-height: 300px;
            overflow-y: auto;
            background-color: #f8f9fa;
            margin-bottom: 20px;
        }
        .messages form textarea {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .messages form button {
            margin-top: 10px;
            background-color: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Job Posts</h2>
        <div class="add-job">
            <a href="/FindHire/hr/add_job.php" class="btn btn-success">Add New Job Post</a>
        </div>
        <?php if ($jobs): ?>
            <?php foreach ($jobs as $job): ?>
                <div class="job-post">
                    <h3><?= htmlspecialchars($job['title']) ?></h3>
                    <p><strong>Description:</strong> <?= htmlspecialchars($job['description']) ?></p>
                    <p><strong>Posted On:</strong> <?= htmlspecialchars($job['created_at']) ?></p>
                    <div class="actions">
                        <a href="view_applications.php?job_id=<?= $job['id'] ?>">
                            <button>View Applications</button>
                        </a>
                        <a href="edit_job.php?job_id=<?= $job['id'] ?>">
                            <button>Edit Job</button>
                        </a>
                        <a href="delete_job.php?job_id=<?= $job['id'] ?>" onclick="return confirm('Are you sure you want to delete this job post?');">
                            <button style="background-color: #dc3545;">Delete Job</button>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No job posts found. Start by adding one!</p>
        <?php endif; ?>

        <div class="messages">
            <h2>Messages from Applicants</h2>
            <form method="GET">
                <label>Applicant:
                    <select name="applicant_id" required onchange="this.form.submit()">
                        <option value="">Select Applicant</option>
                        <?php foreach ($applicants as $applicant): ?>
                            <option value="<?= $applicant['id'] ?>" <?= $applicantId == $applicant['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($applicant['username']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </form>

            <?php if ($applicantId): ?>
                <div class="message-list">
                    <?php foreach ($messages as $msg): ?>
                        <p>
                            <strong><?= htmlspecialchars($msg['sender_name']) ?>:</strong>
                            <?= nl2br(htmlspecialchars($msg['content'])) ?>
                            <small>(<?= htmlspecialchars($msg['sent_at']) ?>)</small>
                        </p>
                    <?php endforeach; ?>
                </div>
                <form method="POST">
                    <input type="hidden" name="applicant_id" value="<?= $applicantId ?>">
                    <textarea name="content" placeholder="Type your reply here..." required></textarea><br>
                    <button type="submit">Send Reply</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="logout">
            <a href="../core/handleForms.php?logout=1">Logout</a>
        </div>
    </div>
</body>
</html>
