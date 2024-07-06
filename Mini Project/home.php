<?php
session_start(); // Start the session

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$database = "mini";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$name = $regNo = $department = $phone = $odType = $permissionStaff = $startDate = $endDate = $purpose = $place = "";

// Get user ID from session
$userId = $_SESSION['userid'] ?? '';

// Fetch user information from the database based on user ID
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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $odType = $_POST['odType'];
    $permissionStaff = $_POST['permissionStaff'];
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $purpose = $_POST['purpose'];
    $place = $_POST['place'];

    // Insert data into the oddata table with default status as "pending"
    $insertSql = "INSERT INTO oddata (name, regNo, department, phone, odType, permissionStaff, startDate, endDate, purpose, place, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";

    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("ssssssssss", $name, $regNo, $department, $phone, $odType, $permissionStaff, $startDate, $endDate, $purpose, $place);

    // Execute the insert statement
    $insertStmt->execute();

    // Check if the insert was successful
    if ($insertStmt->affected_rows === 1) {
        // Redirect to the same page to refresh
        header("Location: ./home.php");
        exit();
    } else {
        // Handle the case where the insert failed
        echo "Error: " . $insertStmt->error;
    }

    // Close the insert statement
    $insertStmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIET OD Management</title>
    <link rel="stylesheet" href="./bootstrap-5.3.2-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="./shome.css">
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-end mt-3">
                <button class="btn btn-danger" onclick="logout()">Logout</button>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card card-transparent p-4" id="formCard">
                <div class="display-6 text-center mb-4"><strong>On Duty Slip</strong></div>
                <form id="studentInfoForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <table class="table mb-4">
                        <tbody>
                            <!-- Display user information -->
                            <tr>
                                <td><strong>Name:</strong></td>
                                <td><?php echo $name; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Registration Number:</strong></td>
                                <td><?php echo $regNo; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Department:</strong></td>
                                <td><?php echo $department; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Phone Number:</strong></td>
                                <td><?php echo $phone; ?></td>
                            </tr>
                            <!-- Add other fields as needed -->
                            <tr>
                                <td><strong>Type of OD:</strong></td>
                                <td>
                                    <select class="form-select" id="odTypeInput" name="odType" required onchange="togglePermissionStaff()">
                                        <option value="">Select Type of OD</option>
                                        <option value="full">Full OD</option>
                                        <option value="permission">Permission OD</option>
                                    </select>
                                </td>
                            </tr>
                            <tr id="permissionStaffRow" style="display: none;">
                                <td><strong>Permission Staff:</strong></td>
                                <td><input type="text" class="form-control" id="permissionStaffInput" name="permissionStaff"></td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Form fields for entering additional data -->
                    <div class="form-row mb-3">
                        <div class="col-md-6">
                            <label for="startDateInput" class="form-label">Start Date:</label>
                            <input type="date" class="form-control" id="startDateInput" name="startDate" required onchange="updateEndDateMin()">
                        </div>
                        <div class="col-md-6">
                            <label for="endDateInput" class="form-label">End Date:</label>
                            <input type="date" class="form-control" id="endDateInput" name="endDate" required>
                        </div>
                    </div>
                    <div class="form-row mb-3">
                        <div class="col-md-6">
                            <label for="purposeInput" class="form-label">Purpose:</label>
                            <textarea class="form-control" id="purposeInput" name="purpose" rows="3" placeholder="Enter the purpose" required></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="placeInput" class="form-label">Place of Visit:</label>
                            <input type="text" class="form-control" id="placeInput" name="place" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-primary btn-block" onclick="submitForm()">Submit</button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-info btn-block" onclick="redirectToStatus()">Check Status</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function logout() {
        // Add logic to logout the user and redirect to login page
        alert('Logout successful'); // Example alert
        // Redirect to login page
        window.location.href = './index.php'; // Change the URL to your login page
    }

    function redirectToStatus() {
        window.location.href = './user_view.php?regno=<?php echo $regNo; ?>';
    }

    function togglePermissionStaff() {
        var odTypeInput = document.getElementById('odTypeInput');
        var permissionStaffRow = document.getElementById('permissionStaffRow');
        var permissionStaffInput = document.getElementById('permissionStaffInput');
        
        if (odTypeInput.value === 'permission') {
            permissionStaffRow.style.display = 'table-row';
            permissionStaffInput.setAttribute('required', 'true');
        } else {
            permissionStaffRow.style.display = 'none';
            permissionStaffInput.removeAttribute('required');
        }
    }

    function submitForm() {
        var form = document.getElementById("studentInfoForm");
        if (form.checkValidity()) {
            // If form is valid, submit it
            form.submit();
            // Show success message
            showAlert("Form submitted successfully!");
        } else {
            alert("Please fill in all required fields.");
        }
    }

    function showAlert(message) {
        alert(message);
    }

    function updateEndDateMin() {
        var startDateInput = document.getElementById('startDateInput');
        var endDateInput = document.getElementById('endDateInput');
        endDateInput.min = startDateInput.value;
    }
</script>
</body>
</html>
