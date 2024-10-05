<?php
$host = 'localhost';
$db = 'emc';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // استعلام للتحقق من البريد الإلكتروني
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // التحقق من كلمة المرور
        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            // توجيه المستخدم بناءً على دوره
            switch ($user['role']) {
                case 'student':
                    header("Location:../student/student_dashboard.php");
                    break;
                case 'financial_manager':
                    header("Location: financial_manager_dashboard.php");
                    break;
                case 'registrar':
                    header("Location: registrar_dashboard.php");
                    break;
                case 'system_owner':
                    header("Location: owner_dashboard.php");
                    break;
                case 'results_unit':
                    header("Location: results_unit_dashboard.php");
                    break;
                default:
                    $error_message = "دور المستخدم غير معروف.";
            }
            exit();
        } else {
            $error_message = "كلمة المرور غير صحيحة.";
        }
    } else {
        $error_message = "البريد الإلكتروني غير موجود.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كلية الإمارات للعلوم والتكنولوجيا</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        .error {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="student/logo.png" alt="كلية الإمارات للعلوم والتكنولوجيا">
            <h2>تسجيل الدخول</h2>
        </div>
        <div class="form-container">
            <form method="POST">
                <div class="form-group">
                    <input type="email" name="email" placeholder="البريد الإلكتروني" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="كلمة المرور" required>
                </div>
                <button type="submit" class="btn">تسجيل الدخول</button>

                <!-- عرض رسالة الخطأ إن وجدت -->
                <?php if (!empty($error_message)): ?>
                    <div class="error"><?php echo $error_message; ?></div>
                <?php endif; ?>
            </form>
            <a href="student/register.php" class="signup-link">تسجيل حساب جديد</a>
        </div>
    </div>
</body>
</html>
