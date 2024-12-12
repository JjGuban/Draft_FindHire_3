<?php
session_start();
require '../core/dbConfig.php';
require '../core/models.php';

if ($_SESSION['role'] !== 'Applicant') {
    header("Location: ../dashboard.php");
    exit;
}

$applicantId = $_SESSION['user_id'];

// Initialize $selectedHR to avoid undefined variable warning
$selectedHR = null;

// Fetch HR representatives
$stmt = $pdo->query("SELECT id, username FROM users WHERE role = 'HR'");
$hrUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Safely handle the 'hr_id' parameter from GET request
$selectedHR = $_GET['hr_id'] ?? null;

// Fetch messages with a specific HR
$messages = $selectedHR ? getMessages($applicantId, $selectedHR) : [];

// Handle message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receiverId = $_POST['hr_id'];
    $content = trim($_POST['content']);

    if (!empty($content)) {
        sendMessage($applicantId, $receiverId, $content);
        $success = "Message sent successfully.";
        $messages = getMessages($applicantId, $receiverId); // Refresh messages
    } else {
        $error = "Message content cannot be empty.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applicant - Follow Up</title>
</head>
<body>
    <h1>Message HR</h1>
    <?php if (!empty($error)) echo "<p style='color: red;'>$error</p>"; ?>
    <?php if (!empty($success)) echo "<p style='color: green;'>$success</p>"; ?>
    <form method="GET">
        <label>HR Representative:
            <select name="hr_id" required onchange="this.form.submit()">
                <option value="">Select HR</option>
                <?php foreach ($hrUsers as $hr): ?>
                    <option value="<?= $hr['id'] ?>" <?= $selectedHR == $hr['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($hr['username']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
    </form>

    <?php if ($selectedHR): ?>
        <div>
            <h2>Messages with <?= htmlspecialchars($hrUsers[array_search($selectedHR, array_column($hrUsers, 'id'))]['username']) ?></h2>
            <div style="border: 1px solid #ccc; padding: 10px; max-height: 300px; overflow-y: auto;">
                <?php foreach ($messages as $msg): ?>
                    <p>
                        <strong><?= htmlspecialchars($msg['sender_name']) ?>:</strong>
                        <?= nl2br(htmlspecialchars($msg['content'])) ?>
                        <small>(<?= htmlspecialchars($msg['sent_at']) ?>)</small>
                    </p>
                <?php endforeach; ?>
            </div>
            <form method="POST">
                <input type="hidden" name="hr_id" value="<?= $selectedHR ?>">
                <textarea name="content" placeholder="Type your message here..." required></textarea><br>
                <button type="submit">Send Follow-Up</button>
            </form>
        </div>
    <?php endif; ?>
    <div>
        <a href="apply_job.php" style="display: inline-block; margin-top: 20px; padding: 10px 15px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;">Back to Job Listings</a>
    </div>
</body>
</html>