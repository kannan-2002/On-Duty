<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $servername = "localhost";
    $dbusername = "root";
    $dbpassword = "";
    $database = "mini";
    
    $conn = new mysqli($servername, $dbusername, $dbpassword, $database);
    
    if ($conn->connect_error) { 
        die("Connection failed: " . $conn->connect_error); 
    }

    $userid   = $conn->real_escape_string($_POST['userid']);
    $password = $conn->real_escape_string($_POST['password']);
    $userType = $conn->real_escape_string($_POST['userType']);
    
    $sql = "SELECT * FROM userlog WHERE userid='$userid' AND password='$password' AND userType='$userType'";
    $result = $conn->query($sql);
    
    if ($result->num_rows == 1) {
        $_SESSION['userid'] = $userid;
        $_SESSION['userType'] = $userType;
        
        if ($userType === 'user') {
            header("Location: home.php");
            exit();
        } elseif ($userType === 'admin') {
            header("Location: admin_home.php");
            exit();
        }
    } else {
        $error = "Invalid credentials. Please try again.";
    }
    
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - OD Management System</title>
    <link rel="stylesheet" href="./main.css">
</head>
<body>
    <div class="main-wrapper">
        <div class="card fade-in" style="max-width: 480px; width: 100%;">
            <div class="card-header">
                <h1>🎓 OD Management</h1>
                <p style="color: rgba(255,255,255,0.9); margin-top: 8px; font-size: 14px;">Welcome back! Please login to continue</p>
            </div>
            
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <span>⚠️</span>
                        <span><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="userid" class="form-label">User ID</label>
                        <input type="text" id="userid" name="userid" class="form-control" placeholder="Enter your user ID" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                    </div>

                    <div class="form-group">
                        <label for="userType" class="form-label">Login As</label>
                        <select id="userType" name="userType" class="form-select" required>
                            <option value="user">👤 Student</option>
                            <option value="admin">👨‍💼 Administrator</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block" name="loginBtn">
                        <span>Login</span>
                        <span>→</span>
                    </button>
                </form>

                <div style="margin-top: 24px; text-align: center; color: var(--secondary); font-size: 14px;">
                    <p>Need help? Contact administrator</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>