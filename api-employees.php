<?php
// ─────────────────────────────────────────────
// api-employees.php
// JSON API برای کامپوننت شبکه همکاران
// این فایل باید کنار personal-resume-list.php قرار بگیرد
// ─────────────────────────────────────────────

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// --- تنظیمات دیتابیس ---
$servername = "localhost";
$username   = "erfantey_fantasticfour";
$password   = "Diamond19971376@macsa";
$dbname     = "erfantey_macsacharity";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'اتصال به دیتابیس برقرار نشد.']);
    exit;
}

$conn->set_charset("utf8mb4");

// بررسی وجود ستون is_active
$col_check     = $conn->query("SHOW COLUMNS FROM `employee_profiles` LIKE 'is_active'");
$has_is_active = ($col_check && $col_check->num_rows > 0);

if ($has_is_active) {
    $sql = "SELECT id, fullname, profile_pic, branch, role, job_category
            FROM employee_profiles
            WHERE is_active = 1
            ORDER BY id DESC";
} else {
    $sql = "SELECT id, fullname, profile_pic, branch, role, job_category
            FROM employee_profiles
            ORDER BY id DESC";
}

$result    = $conn->query($sql);
$employees = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $employees[] = [
            'id'           => (int)$row['id'],
            'fullname'     => $row['fullname']     ?? '',
            'profile_pic'  => $row['profile_pic']  ?? '',
            'branch'       => $row['branch']        ?? '',
            'role'         => $row['role']          ?? '',
            'job_category' => $row['job_category'] ?? '',
        ];
    }
}

$conn->close();
echo json_encode($employees, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);