<?php
session_start();

// التأكد من أن الطالب قام بتسجيل الدخول
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_name = $_SESSION['student_name'];  // استرجاع اسم الطالب من الجلسة
$language = isset($_SESSION['language']) ? $_SESSION['language'] : 'ar'; // اللغة الافتراضية

// التأكد من التحقق
$verified = isset($_SESSION['verified']) ? $_SESSION['verified'] : false;

// تغيير اللغة عند الطلب
if (isset($_GET['change_lang'])) {
    $language = $_GET['change_lang'] == 'ar' ? 'en' : 'ar';
    $_SESSION['language'] = $language;
}

// الاتصال بقاعدة البيانات
$mysqli = new mysqli("localhost", "username", "password", "emc");
if ($mysqli->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $mysqli->connect_error);
}

// الحصول على بيانات الطالب الذي قام بتسجيل الدخول
$student_id = $_SESSION['student_id'];
$query = "SELECT full_name, registration_date FROM students WHERE id = $student_id";
$result = $mysqli->query($query);
$student = $result->fetch_assoc();

// معالجة تقديم الطلب
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $specialization = $_POST['specialization'];
    $submission_date = date('Y-m-d');

    // إدراج بيانات التحقق في جدول التحقق
    $stmt = $mysqli->prepare("INSERT INTO verification_requests (student_id, full_name, specialization, submission_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $student_id, $student['full_name'], $specialization, $submission_date);
    $stmt->execute();
    $stmt->close();
}

// جلب بيانات التحقق إن وجدت
$query_verifications = "SELECT full_name, specialization, submission_date FROM verification_requests WHERE student_id = $student_id";
$result_verifications = $mysqli->query($query_verifications);

?>

<!DOCTYPE html>
<html lang="<?php echo $language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم الطالب</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style/style2.css"> <!-- تأكد من ربط ملف CSS -->
</head>
<body dir="<?php echo $language == 'ar' ? 'rtl' : 'ltr'; ?>">

    <!-- Header Section -->
    <header>
        <div class="logo">
            <img src="student/logo.png" alt="شعار كلية الإمارات للعلوم والتكنولوجيا">
        </div>
        <div class="user-menu">
            <img src="student/logouser.png" alt="أيقونة المستخدم" class="user-icon" onclick="toggleDropdown()">
            <a href="?change_lang=<?php echo $language == 'ar' ? 'en' : 'ar'; ?>" class="language-toggle">
                <?php echo $language == 'ar' ? "English" : "عربي"; ?>
            </a>
            <div id="userDropdown" class="dropdown-content">
                <a href="#" onclick="toggleChangePassword()">تغيير كلمة المرور</a>
                <a href="logout.php">تسجيل الخروج</a>
            </div>
        </div>
    </header>

    <!-- Sidebar Section -->
    <div class="sidebar">
        <a href="#" onclick="loadVerification()">طلب تحقق</a>
        <a href="request_graduation_certificate.php">طلب شهادة تخرج</a>
        <a href="request_enrollment_certificate.php">طلب شهادة قيد</a>
        <a href="request_academic_record.php">طلب سجل أكاديمي</a>
        <a href="request_documentation.php">طلب توثيق</a>
        <a href="request_submission.php">طلب تسليم</a>
        <a href="logout.php">خروج</a>
    </div>

    <!-- Main Content Section -->
    <div class="main-content">
        <?php if (!$verified): ?>
            <h3>لم يتم التحقق من طلبك بعد. لا يمكنك تقديم الطلبات حتى يتم التحقق.</h3>
        <?php else: ?>
            <!-- عرض نموذج التخصص -->
            <h2>تقديم طلب التحقق</h2>
            <table class="table table-bordered">
                <tr>
                    <th>الاسم الكامل</th>
                    <th>تاريخ التسجيل</th>
                </tr>
                <tr>
                    <td><?php echo $student['full_name']; ?></td>
                    <td><?php echo $student['registration_date']; ?></td>
                </tr>
            </table>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="specialization">اختر التخصص:</label>
                    <select id="specialization" name="specialization" class="form-control">
                        <option value="هندسة الحاسوب">هندسة الحاسوب</option>
                        <option value="علوم الحاسوب">علوم الحاسوب</option>
                        <option value="الهندسة المدنية">الهندسة المدنية</option>
                        <option value="إدارة الأعمال">إدارة الأعمال</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">تقديم للتحقق</button>
            </form>

            <hr>

            <!-- عرض جدول الطلبات المقدمة -->
            <h3>طلبات التحقق المقدمة:</h3>
            <table class="table table-bordered">
                <tr>
                    <th>الاسم الكامل</th>
                    <th>التخصص</th>
                    <th>تاريخ التقديم</th>
                </tr>
                <?php while ($verification = $result_verifications->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $verification['full_name']; ?></td>
                    <td><?php echo $verification['specialization']; ?></td>
                    <td><?php echo $verification['submission_date']; ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        <?php endif; ?>
    </div>

    <!-- Footer Section -->
    <footer>
        <p>جميع حقوق النشر محفوظة لدى كلية الإمارات للعلوم والتكنولوجيا - عزام كمال يحيى آدم</p>
    </footer>

    <script>
        function toggleDropdown() {
            var dropdown = document.getElementById("userDropdown");
            dropdown.classList.toggle("show");
        }

        function toggleChangePassword() {
            var form = document.getElementById("change-password-form");
            if (form.style.display === "none") {
                form.style.display = "block";
            } else {
                form.style.display = "none";
            }
        }

        window.onclick = function(event) {
            if (!event.target.matches('.user-icon')) {
                var dropdowns = document.getElementsByClassName("dropdown-content");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }
    </script>

</body>
</html>
