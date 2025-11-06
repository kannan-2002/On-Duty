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

$regNo = $_GET['regNo'] ?? '';
$submissionDate = $_GET['submissionDate'] ?? '';

$sql = "SELECT * FROM oddata WHERE regNo = ? AND submission_datetime = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $regNo, $submissionDate);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View OD Request Details</title>
    <link rel="stylesheet" href="./main.css">
</head>
<body>
    <div class="navbar">
        <div class="container">
            <div class="navbar-content">
                <div class="navbar-brand">👨‍💼 Request Details</div>
                <button class="btn btn-secondary" onclick="window.location.href='admin_home.php'">
                    <span>← Back to Dashboard</span>
                </button>
            </div>
        </div>
    </div>

    <div class="container" style="padding: 40px 20px;">
        <?php if ($result->num_rows > 0): ?>
            <?php $row = $result->fetch_assoc(); ?>
            
            <div class="page-header fade-in">
                <h1>📄 OD Request Details</h1>
                <p>Complete information about the submitted request</p>
            </div>

            <div class="card fade-in" style="max-width: 1000px; margin: 0 auto;">
                <div class="card-body">
                    <!-- Status Badge -->
                    <div style="text-align: center; margin-bottom: 30px;">
                        <?php if ($row['status'] == 'Approved'): ?>
                            <span class="badge badge-success" style="font-size: 16px; padding: 12px 24px;">✓ APPROVED</span>
                        <?php elseif ($row['status'] == 'Rejected'): ?>
                            <span class="badge badge-danger" style="font-size: 16px; padding: 12px 24px;">✗ REJECTED</span>
                        <?php else: ?>
                            <span class="badge badge-pending" style="font-size: 16px; padding: 12px 24px;">⏳ PENDING</span>
                        <?php endif; ?>
                    </div>

                    <!-- Student Information -->
                    <h3 style="margin-bottom: 20px; color: var(--primary);">👤 Student Information</h3>
                    <div class="info-grid">
                        <div class="info-box">
                            <div class="info-box-label">Name</div>
                            <div class="info-box-value"><?php echo htmlspecialchars($row['name']); ?></div>
                        </div>
                        <div class="info-box">
                            <div class="info-box-label">Registration No</div>
                            <div class="info-box-value"><?php echo htmlspecialchars($row['regNo']); ?></div>
                        </div>
                        <div class="info-box">
                            <div class="info-box-label">Department</div>
                            <div class="info-box-value"><?php echo htmlspecialchars($row['department']); ?></div>
                        </div>
                        <div class="info-box">
                            <div class="info-box-label">Phone Number</div>
                            <div class="info-box-value"><?php echo htmlspecialchars($row['phone']); ?></div>
                        </div>
                    </div>

                    <!-- OD Details -->
                    <h3 style="margin: 40px 0 20px; color: var(--primary);">📋 OD Details</h3>
                    <div class="info-grid">
                        <div class="info-box">
                            <div class="info-box-label">OD Type</div>
                            <div class="info-box-value">
                                <?php 
                                if ($row['odType'] === 'permission') {
                                    echo '🔓 Permission OD';
                                } else {
                                    echo '📅 Full OD';
                                }
                                ?>
                            </div>
                        </div>
                        <?php if ($row['odType'] === 'permission' && !empty($row['permissionStaff'])): ?>
                        <div class="info-box">
                            <div class="info-box-label">Permission Staff</div>
                            <div class="info-box-value"><?php echo htmlspecialchars($row['permissionStaff']); ?></div>
                        </div>
                        <?php endif; ?>
                        <div class="info-box">
                            <div class="info-box-label">Start Date</div>
                            <div class="info-box-value"><?php echo date('M d, Y', strtotime($row['startDate'])); ?></div>
                        </div>
                        <div class="info-box">
                            <div class="info-box-label">End Date</div>
                            <div class="info-box-value"><?php echo date('M d, Y', strtotime($row['endDate'])); ?></div>
                        </div>
                        <div class="info-box">
                            <div class="info-box-label">Place of Visit</div>
                            <div class="info-box-value"><?php echo htmlspecialchars($row['place']); ?></div>
                        </div>
                        <div class="info-box">
                            <div class="info-box-label">Submission Date</div>
                            <div class="info-box-value"><?php echo date('M d, Y h:i A', strtotime($row['submission_datetime'])); ?></div>
                        </div>
                    </div>

                    <!-- Purpose -->
                    <div class="info-box" style="margin-top: 20px;">
                        <div class="info-box-label">Purpose</div>
                        <div class="info-box-value"><?php echo nl2br(htmlspecialchars($row['purpose'])); ?></div>
                    </div>

                    <!-- Photo Proof -->
                    <?php if (!empty($row['photo_proof'])): ?>
                    <h3 style="margin: 40px 0 20px; color: var(--primary);">📷 Photo Proof</h3>
                    <div style="text-align: center;">
                        <button class="btn btn-primary" onclick="togglePhoto()" id="photoToggle">
                            <span>👁 View Photo</span>
                        </button>
                        <div id="photoContainer" style="display: none; margin-top: 20px;">
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($row['photo_proof']); ?>" 
                                 style="max-width: 100%; height: auto; border-radius: var(--radius); box-shadow: var(--shadow-lg);" 
                                 alt="Photo Proof">
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info" style="margin-top: 30px;">
                        <span>ℹ️</span>
                        <span>No photo proof has been uploaded yet.</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <div class="card fade-in" style="max-width: 600px; margin: 0 auto;">
                <div class="card-body text-center">
                    <div style="font-size: 64px; margin-bottom: 20px;">❌</div>
                    <h2>No Data Found</h2>
                    <p style="color: var(--secondary); margin: 20px 0;">The requested OD information could not be found.</p>
                    <button class="btn btn-primary" onclick="window.location.href='admin_home.php'">
                        <span>← Back to Dashboard</span>
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function togglePhoto() {
            const container = document.getElementById('photoContainer');
            const button = document.getElementById('photoToggle');
            
            if (container.style.display === 'none') {
                container.style.display = 'block';
                button.innerHTML = '<span>🙈 Hide Photo</span>';
            } else {
                container.style.display = 'none';
                button.innerHTML = '<span>👁 View Photo</span>';
            }
        }
    </script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>