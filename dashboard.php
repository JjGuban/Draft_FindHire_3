<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Redirect based on role
if ($_SESSION['role'] === 'HR') {
    header("Location: hr/job_posts.php");
    exit;
} elseif ($_SESSION['role'] === 'Applicant') {
    header("Location: applicant/apply_job.php");
    exit;
} else {
    echo "Invalid user role.";
    session_destroy();
    exit;
}
?>
