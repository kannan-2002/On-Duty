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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['regNo']) && isset($_POST['status'])) {
    $regNo = $_POST['regNo'];
    $status = $_POST['status'];
    $submissionDate = $_POST['submissionDate'];

    $stmt = $conn->prepare("UPDATE oddata SET status = ? WHERE regNo = ? AND submission_datetime = ?");
    $stmt->bind_param("sss", $status, $regNo, $submissionDate);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Status updated to $status successfully!";
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
    $stmt->close();
}

$sql = "SELECT name, regNo, department, status, submission_datetime FROM oddata ORDER BY submission_datetime DESC";
$result = $conn->query($sql);

$users = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - OD Management</title>
    <link rel="stylesheet" href="./main.css">
</head>
<body>
    <div class="navbar">
        <div class="container">
            <div class="navbar-content">
                <div class="navbar-brand">👨‍💼 Admin Dashboard</div>
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

        <div class="page-header fade-in">
            <h1>📋 Student OD Requests</h1>
            <p>Review and manage on-duty requests from students</p>
        </div>

        <div class="search-container slide-in">
            <div class="search-wrapper">
                <span class="search-icon">🔍</span>
                <input type="text" id="searchInput" class="search-input" placeholder="Search by name or department..." onkeyup="searchTable()">
            </div>
        </div>

        <div class="table-wrapper slide-in">
            <div class="table-container">
                <table class="table" id="studentsTable">
                    <thead>
                        <tr>
                            <th>Submission Date</th>
                            <th>Name</th>
                            <th>Registration No</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="6" class="text-center" style="padding: 40px;">
                                    <div style="color: var(--secondary);">
                                        <div style="font-size: 48px; margin-bottom: 16px;">📭</div>
                                        <div style="font-size: 18px; font-weight: 600;">No requests found</div>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo date('M d, Y h:i A', strtotime($user['submission_datetime'])); ?></td>
                                <td><strong><?php echo htmlspecialchars($user['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($user['regNo']); ?></td>
                                <td><?php echo htmlspecialchars($user['department']); ?></td>
                                <td>
                                    <?php if ($user['status'] == 'Approved'): ?>
                                        <span class="badge badge-success">✓ Approved</span>
                                    <?php elseif ($user['status'] == 'Rejected'): ?>
                                        <span class="badge badge-danger">✗ Rejected</span>
                                    <?php else: ?>
                                        <span class="badge badge-pending">⏳ Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-2" style="flex-wrap: wrap;">
                                        <?php if ($user['status'] !== 'Approved' && $user['status'] !== 'Rejected'): ?>
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="regNo" value="<?php echo $user['regNo']; ?>">
                                                <input type="hidden" name="submissionDate" value="<?php echo $user['submission_datetime']; ?>">
                                                <button type="submit" name="status" value="Approved" class="btn btn-success btn-sm">
                                                    <span>✓ Approve</span>
                                                </button>
                                            </form>
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="regNo" value="<?php echo $user['regNo']; ?>">
                                                <input type="hidden" name="submissionDate" value="<?php echo $user['submission_datetime']; ?>">
                                                <button type="submit" name="status" value="Rejected" class="btn btn-danger btn-sm">
                                                    <span>✗ Reject</span>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <button class="btn btn-info btn-sm" onclick="window.location.href='admin_view.php?regNo=<?php echo $user['regNo']; ?>&submissionDate=<?php echo $user['submission_datetime']; ?>'">
                                            <span>👁 View</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function searchTable() {
            const input = document.getElementById("searchInput");
            const filter = input.value.toUpperCase();
            const table = document.getElementById("studentsTable");
            const tr = table.getElementsByTagName("tr");
            
            for (let i = 1; i < tr.length; i++) {
                const tdName = tr[i].getElementsByTagName("td")[1];
                const tdDept = tr[i].getElementsByTagName("td")[3];
                
                if (tdName && tdDept) {
                    const txtValueName = tdName.textContent || tdName.innerText;
                    const txtValueDept = tdDept.textContent || tdDept.innerText;
                    
                    if (txtValueName.toUpperCase().indexOf(filter) > -1 || 
                        txtValueDept.toUpperCase().indexOf(filter) > -1) {
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