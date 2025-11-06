<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$database = "mini";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$name = $regNo = $department = $phone = $odType = $permissionStaff = $startDate = $endDate = $purpose = $place = "";
$userId = $_SESSION['userid'] ?? '';

$sql = "SELECT u.name, u.regNo, u.department, u.phone
        FROM userdata u
        INNER JOIN userlog l ON u.userid = l.userid
        WHERE u.userid = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $name = $row['name'];
    $regNo = $row['regNo'];
    $department = $row['department'];
    $phone = $row['phone'];
}
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $odType = $_POST['odType'];
    $permissionStaff = $_POST['permissionStaff'] ?? '';
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $purpose = $_POST['purpose'];
    $place = $_POST['place'];

    $insertSql = "INSERT INTO oddata (name, regNo, department, phone, odType, permissionStaff, startDate, endDate, purpose, place, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";

    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("ssssssssss", $name, $regNo, $department, $phone, $odType, $permissionStaff, $startDate, $endDate, $purpose, $place);
    $insertStmt->execute();

    if ($insertStmt->affected_rows === 1) {
        $_SESSION['success_message'] = "OD request submitted successfully!";
        header("Location: ./home.php");
        exit();
    }
    $insertStmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit OD Request - CIET</title>
    <link rel="stylesheet" href="./main.css">
</head>
<body>
    <div class="navbar">
        <div class="container">
            <div class="navbar-content">
                <div class="navbar-brand">🎓 OD Management</div>
                <button class="btn btn-danger" onclick="window.location.href='./index.php'">
                    <span>Logout</span>
                    <span>→</span>
                </button>
            </div>
        </div>
    </div>

    <div class="container" style="padding: 40px 20px;">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success fade-in">
                <span>✓</span>
                <span><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></span>
            </div>
        <?php endif; ?>

        <div class="card fade-in" style="max-width: 900px; margin: 0 auto;">
            <div class="card-header">
                <h1>📝 On Duty Request Form</h1>
            </div>

            <div class="card-body">
                <!-- Student Information -->
                <div class="info-grid">
                    <div class="info-box">
                        <div class="info-box-label">Name</div>
                        <div class="info-box-value"><?php echo htmlspecialchars($name); ?></div>
                    </div>
                    <div class="info-box">
                        <div class="info-box-label">Registration No</div>
                        <div class="info-box-value"><?php echo htmlspecialchars($regNo); ?></div>
                    </div>
                    <div class="info-box">
                        <div class="info-box-label">Department</div>
                        <div class="info-box-value"><?php echo htmlspecialchars($department); ?></div>
                    </div>
                    <div class="info-box">
                        <div class="info-box-label">Phone Number</div>
                        <div class="info-box-value"><?php echo htmlspecialchars($phone); ?></div>
                    </div>
                </div>

                <!-- OD Request Form -->
                <form id="odForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="form-group">
                        <label for="odType" class="form-label">Type of OD</label>
                        <select class="form-select" id="odType" name="odType" required onchange="togglePermissionStaff()">
                            <option value="">Select Type of OD</option>
                            <option value="full">Full OD</option>
                            <option value="permission">Permission OD</option>
                        </select>
                    </div>

                    <div class="form-group" id="permissionStaffGroup" style="display: none;">
                        <label for="permissionStaff" class="form-label">Permission Staff Name</label>
                        <input type="text" class="form-control" id="permissionStaff" name="permissionStaff" placeholder="Enter staff name">
                    </div>

                    <div class="d-flex gap-3" style="flex-wrap: wrap;">
                        <div class="form-group" style="flex: 1; min-width: 250px;">
                            <label for="startDate" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="startDate" name="startDate" required onchange="updateEndDateMin()">
                        </div>
                        <div class="form-group" style="flex: 1; min-width: 250px;">
                            <label for="endDate" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="endDate" name="endDate" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="place" class="form-label">Place of Visit</label>
                        <input type="text" class="form-control" id="place" name="place" placeholder="Enter place of visit" required>
                    </div>

                    <div class="form-group">
                        <label for="purpose" class="form-label">Purpose</label>
                        <textarea class="form-control" id="purpose" name="purpose" rows="4" placeholder="Enter the purpose of OD request" required></textarea>
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">
                            <span>📤 Submit Request</span>
                        </button>
                        <button type="button" class="btn btn-info" onclick="window.location.href='./user_view.php?regno=<?php echo $regNo; ?>'">
                            <span>📊 Check Status</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function togglePermissionStaff() {
            const odType = document.getElementById('odType').value;
            const permissionGroup = document.getElementById('permissionStaffGroup');
            const permissionInput = document.getElementById('permissionStaff');
            
            if (odType === 'permission') {
                permissionGroup.style.display = 'block';
                permissionInput.setAttribute('required', 'true');
            } else {
                permissionGroup.style.display = 'none';
                permissionInput.removeAttribute('required');
                permissionInput.value = '';
            }
        }

        function updateEndDateMin() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate');
            endDate.min = startDate;
            if (endDate.value && endDate.value < startDate) {
                endDate.value = startDate;
            }
        }

        // Set min date to today
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('startDate').min = today;
        document.getElementById('endDate').min = today;
    </script>
</body>
</html>