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

// Handle file upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['file']) && isset($_POST['regNo']) && isset($_POST['submissionDatetime'])) {
    $regNo = $_POST['regNo'];
    $submissionDatetime = $_POST['submissionDatetime'];
    $file = $_FILES['file'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $fileContent = file_get_contents($file['tmp_name']);
        $updateSql = "UPDATE oddata SET photo_proof = ? WHERE regNo = ? AND submission_datetime = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("sss", $fileContent, $regNo, $submissionDatetime);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Proof uploaded successfully!";
            echo "success";
        } else {
            echo "error: " . $conn->error;
        }
        $stmt->close();
    } else {
        echo "error";
    }
    exit();
}

$regNo = $_GET['regno'] ?? '';

$sql = "SELECT name, regNo, startDate, endDate, purpose, place, IFNULL(status, 'pending') AS status, submission_datetime, photo_proof FROM oddata WHERE regNo = ? ORDER BY submission_datetime DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $regNo);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My OD Requests Status</title>
    <link rel="stylesheet" href="./main.css">
</head>
<body>
    <div class="navbar">
        <div class="container">
            <div class="navbar-content">
                <div class="navbar-brand">📊 My OD Status</div>
                <button class="btn btn-secondary" onclick="window.location.href='home.php'">
                    <span>← Back to Home</span>
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
            <h1>📋 My OD Request History</h1>
            <p>View and manage your on-duty requests</p>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="table-wrapper slide-in">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Submission Date</th>
                                <th>Duration</th>
                                <th>Place</th>
                                <th>Purpose</th>
                                <th>Status</th>
                                <th>Proof</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('M d, Y h:i A', strtotime($row['submission_datetime'])); ?></td>
                                <td>
                                    <strong><?php echo date('M d', strtotime($row['startDate'])); ?></strong>
                                    <span style="color: var(--secondary);"> to </span>
                                    <strong><?php echo date('M d', strtotime($row['endDate'])); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($row['place']); ?></td>
                                <td>
                                    <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($row['purpose']); ?>">
                                        <?php echo htmlspecialchars($row['purpose']); ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($row['status'] == 'Approved'): ?>
                                        <span class="badge badge-success">✓ Approved</span>
                                    <?php elseif ($row['status'] == 'Rejected'): ?>
                                        <span class="badge badge-danger">✗ Rejected</span>
                                    <?php else: ?>
                                        <span class="badge badge-pending">⏳ Pending</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($row['photo_proof'])): ?>
                                        <div class="badge badge-success">✓ Verified</div>
                                    <?php else: ?>
                                        <button class="btn btn-primary btn-sm" onclick="uploadProof('<?php echo $row['regNo']; ?>', '<?php echo $row['submission_datetime']; ?>', this)">
                                            <span>📤 Upload</span>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="card fade-in" style="max-width: 600px; margin: 0 auto;">
                <div class="card-body text-center">
                    <div style="font-size: 64px; margin-bottom: 20px;">📭</div>
                    <h2>No Requests Found</h2>
                    <p style="color: var(--secondary); margin: 20px 0;">You haven't submitted any OD requests yet.</p>
                    <button class="btn btn-primary" onclick="window.location.href='home.php'">
                        <span>📝 Create New Request</span>
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function uploadProof(regNo, submissionDatetime, button) {
            const fileInput = document.createElement('input');
            fileInput.setAttribute('type', 'file');
            fileInput.setAttribute('accept', 'image/*');
            
            fileInput.addEventListener('change', function() {
                const file = fileInput.files[0];
                if (file) {
                    // Show loading state
                    const originalHTML = button.innerHTML;
                    button.innerHTML = '<span>⏳ Uploading...</span>';
                    button.disabled = true;
                    
                    const xhr = new XMLHttpRequest();
                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('regNo', regNo);
                    formData.append('submissionDatetime', submissionDatetime);
                    
                    xhr.open('POST', '<?php echo $_SERVER['PHP_SELF']; ?>', true);
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4) {
                            if (xhr.status === 200 && xhr.responseText === 'success') {
                                button.innerHTML = '<div class="badge badge-success">✓ Verified</div>';
                                alert('✓ Proof uploaded successfully!');
                            } else {
                                button.innerHTML = originalHTML;
                                button.disabled = false;
                                alert('❌ Failed to upload proof. Please try again.');
                            }
                        }
                    };
                    xhr.send(formData);
                }
            });
            
            fileInput.click();
        }
    </script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>