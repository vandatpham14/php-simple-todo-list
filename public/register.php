<?php
require_once '../config/db.php';

$errors = [];
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // 1. Kiểm tra (Validate) dữ liệu
    if (empty($username)) {
        $errors[] = 'Tên đăng nhập là bắt buộc.';
    }
    if (empty($email)) {
        $errors[] = 'Email là bắt buộc.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email không hợp lệ.';
    }
    if (empty($password)) {
        $errors[] = 'Mật khẩu là bắt buộc.';
    }
    if ($password !== $password_confirm) {
        $errors[] = 'Mật khẩu xác nhận không khớp.';
    }

    // 2. Kiểm tra xem username hoặc email đã tồn tại chưa
    if (empty($errors)) {
        try {
            // Sử dụng Prepared Statement để chống SQL Injection
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            $user = $stmt->fetch();

            if ($user) {
                if ($user['username'] === $username) {
                    $errors[] = 'Tên đăng nhập đã tồn tại.';
                }
                if ($user['email'] === $email) {
                    $errors[] = 'Email đã tồn tại.';
                }
            } else {
                // 3. Băm mật khẩu (Hashing)
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // 4. Thêm người dùng mới vào CSDL
                $stmt_insert = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $stmt_insert->execute([$username, $email, $hashed_password]);

                // Chuyển hướng đến trang đăng nhập sau khi đăng ký thành công
                redirect('login.php?registered=success');
            }
        } catch (PDOException $e) {
            $errors[] = 'Lỗi CSDL: ' . $e->getMessage();
        }
    }
}

// Hiển thị giao diện
include '../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="text-center">Đăng ký tài khoản</h3>
            </div>
            <div class="card-body">
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form action="register.php" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Tên đăng nhập</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">Xác nhận mật khẩu</label>
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Đăng ký</button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center">
                Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>