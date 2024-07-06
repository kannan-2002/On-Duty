<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  <link rel="stylesheet" href="./style.css">
  <style>
    body {
      background-image: url('./Assets/5192479.jpg');
      background-size: cover;
      background-position: center;
    }
  </style>
  <title>Login Page</title>
</head>
<body>

</div>
      <div class="container mt-5 ">
        <div class="row justify-content-center">
          <div class="col-md-6">
            <div class="card transparent-card">
              <div class="card-header">
                <h3 class="text-center text-primary"><strong>Login</strong></h3>
              </div>
              <div class="card-body">
                <form class="m-4 mr-5" id="loginForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                  <div class="form-group">
                    <label for="username" class="text-primary"><strong>User ID:</strong></label>
                    <input type="text" class="form-control" id="userid" name="userid" placeholder="Enter your user ID">
                  </div>
                  <div class="form-group">
                    <label for="password" class="text-primary"><strong>Password:</strong></label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password">
                  </div>
                  <div class="form-group">
                    <label for="userType" class="text-primary"><strong>Login As:</strong></label>
                    <select class="form-control" id="userType" name="userType">
                      <option value="user">User</option>
                      <option value="admin">Admin</option>
                    </select>
                  </div>
                  <button type="submit" class="btn btn-primary" id="loginBtn" name="loginBtn">Login</button>
                
                  <!-- Redirect Modal -->
                  <div class="modal fade" id="redirectModal" tabindex="-1" role="dialog" aria-labelledby="redirectModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title" id="redirectModalLabel">Redirecting...</h5>
                          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                          </button>
                        </div>
                        <div class="modal-body">
                          You will be redirected shortly.
                        </div>
                     
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
      <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    </div>

    <?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection parameters
    $servername = "localhost"; // Replace with your database host
    $username = "root"; // Replace with your database username
    $password = ""; // Replace with your database password
    $database = "mini"; // Replace with your database name
    
    // Create connection
    $conn = new mysqli($servername, $username, $password, $database);
    
    // Check connection
    if ($conn->connect_error) { 
        die("Connection failed: " . $conn->connect_error); 
    }

    // Retrieve form data and sanitize inputs (consider using prepared statements)
    $userid = $conn->real_escape_string($_POST['userid']);
    $password = $conn->real_escape_string($_POST['password']);
    $userType = $conn->real_escape_string($_POST['userType']);
    
    // Query database for user authentication (consider using prepared statements)
    $sql = "SELECT * FROM userlog WHERE userid='$userid' AND password='$password' AND userType='$userType'";
    $result = $conn->query($sql);
    
    // Check if user exists
    if ($result->num_rows == 1) {
        // User authenticated, set session variables
        $_SESSION['userid'] = $userid;
        $_SESSION['userType'] = $userType;
        
        // Redirect the user based on user type
        if ($userType === 'user') {
            header("Location: ./home.php"); // Redirect user to home page
            exit(); // Stop script execution after redirection
        } else if ($userType === 'admin') {
            header("Location: ./admin_home.php"); // Redirect admin to admin home page
            exit(); // Stop script execution after redirection
        }
      
    } else {
        // User authentication failed, redirect back to login page with error message
        echo "<script>alert('Invalid credentials. Please try again.');</script>";
    }
    
    // Close database connection
    $conn->close();
}
?>
</body>
</html>
