<?php
session_start();

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

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['regNo']) && isset($_POST['status'])) {
    // Sanitize input data
    $regNo = $_POST['regNo'];
    $status = $_POST['status'];
    $submissionDate = $_POST['submissionDate']; // Add submission date

    // Prepare and execute SQL statement to update status in the database
    $stmt = $conn->prepare("UPDATE oddata SET status = ? WHERE regNo = ? AND submission_datetime = ?");
    $stmt->bind_param("sss", $status, $regNo, $submissionDate); // Bind submission date

    // Check if the statement executed successfully
    if ($stmt->execute()) {
        // Redirect to the same page after successful submission
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } else {
        // Handle the error if the statement failed to execute
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}

// Fetch data from the database
$sql = "SELECT name, regNo, department, status, submission_datetime FROM oddata";
$result = $conn->query($sql);

// Store retrieved data in an array
$users = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
} else {
    echo "No records found";
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Page</title>
<link rel="stylesheet" href="styless.css">
<link rel="stylesheet" href="status.css">
<style>
    .table-container {
        overflow-x: auto;
    }
</style>
</head>
<body class="body3">

<div class="logout-container">
    <button class="logout-btn" onclick="logout()">Logout</button>
</div>

<h1><center>Students Information</center></h1>

<div class="search-box">
    <h1><center><input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search for students..."></center></h1>
</div>
<div class="table-container">
<table id="studentsTable">
    <tr>
        <th>Submission Date</th>
        <th>Name</th>
        <th>Registration No</th>
        <th>Department</th>
        <th>Status/Action</th>
        <th>View</th>
    </tr>
    <?php foreach ($users as $index => $user): ?>
    <tr>
        <td><?php echo $user['submission_datetime']; ?></td>
        <td><?php echo $user['name']; ?></td>
        <td><?php echo $user['regNo']; ?></td>
        <td><?php echo $user['department']; ?></td>
        <td>
            <?php if ($user['status'] == 'Approved' || $user['status'] == 'Rejected'): ?>
                <button class="status-btn" style="background-color: <?php echo $user['status'] == 'Approved' ? 'rgb(144, 238, 144)' : 'rgb(255, 99, 71)'; ?>"><?php echo $user['status']; ?></button>
            <?php else: ?>
                <form method="post">
                    <input type="hidden" name="regNo" value="<?php echo $user['regNo']; ?>">
                    <input type="hidden" name="submissionDate" value="<?php echo $user['submission_datetime']; ?>"> <!-- Add submission date -->
                    <button type="submit" name="status" value="Approved" class="approve-btn">Approve</button>
                </form>
                <form method="post">
                    <input type="hidden" name="regNo" value="<?php echo $user['regNo']; ?>">
                    <input type="hidden" name="submissionDate" value="<?php echo $user['submission_datetime']; ?>"> <!-- Add submission date -->
                    <button type="submit" name="status" value="Rejected" class="reject-btn">Reject</button>
                </form>
            <?php endif; ?>
        </td>
        <td><button class="view-btn" onclick="redirectToView('<?php echo $user['regNo']; ?>', '<?php echo $user['submission_datetime']; ?>')">View</button></td>
    </tr>
    <?php endforeach; ?>
</table>
</div>

<script>
function logout() {
    // Add logic to logout the user and redirect to login page
    alert('Logout successful'); // Example alert
    // Redirect to login page
    window.location.href = './index.php'; // Change the URL to your login page
}

function redirectToView(regNo, submissionDate) {
    // Redirect to admin_view.php with both regNo and submissionDate parameters
    window.location.href = 'admin_view.php?regNo=' + regNo + '&submissionDate=' + submissionDate;
}

function searchTable() {
    var input, filter, table, tr, tdName, tdDept, i, txtValueName, txtValueDept;
    input = document.getElementById("searchInput");
    filter = input.value.toUpperCase();
    table = document.getElementById("studentsTable");
    tr = table.getElementsByTagName("tr");
    for (i = 0; i < tr.length; i++) {
        tdName = tr[i].getElementsByTagName("td")[1]; // Search by the second column (Name)
        tdDept = tr[i].getElementsByTagName("td")[3]; // Search by the fourth column (Department)
        if (tdName && tdDept) {
            txtValueName = tdName.textContent || tdName.innerText;
            txtValueDept = tdDept.textContent || tdDept.innerText;
            if (txtValueName.toUpperCase().indexOf(filter) > -1 || txtValueDept.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}
</script>

</body>
</html>
