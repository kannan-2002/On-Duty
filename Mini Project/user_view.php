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

// Check if regno is set in the URL
if(isset($_GET['regno'])) {
    $regNo = $_GET['regno'];

    // Fetch data from the database for the specified regno
    $sql = "SELECT name, regNo, startDate, endDate, purpose, place, IFNULL(status, 'pending') AS status, submission_datetime, photo_proof FROM oddata WHERE regNo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $regNo);
    $stmt->execute();
    $result = $stmt->get_result();

    // Display data in the table
    if ($result->num_rows > 0) {
        echo "<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>Submitted Response</title>
    <link rel=\"stylesheet\" href=\"https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css\">
    <link rel=\"stylesheet\" href=\"./status.css\">
    <link rel=\"stylesheet\" href=\"./styless.css\">
    <style>
        .proof-verified {
            background-color: lightblue;
            padding: 5px;
            border-radius: 5px;
        }
    </style>
</head>
<body class=\"body1\">
    <div class=\"container mt-5\">
        <h2 class=\"mb-4\"><center>Submitted Response</center></h2>
        <table class=\"table\">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Reg No</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Purpose</th>
                    <th>Place of Visit</th>
                    <th>Current Status </th>
                    <th>Submission Date</th>
                    <th>Verify Proof</th>
                </tr>
            </thead>
            <tbody>";

        while($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>".$row['name']."</td>
                    <td>".$row['regNo']."</td>
                    <td>".$row['startDate']."</td>
                    <td>".$row['endDate']."</td>
                    <td>".$row['purpose']."</td>
                    <td>".$row['place']."</td>
                    <td>".$row['status']."</td>
                    <td>".$row['submission_datetime']."</td>
                    <td>";
            // Check if photo proof is already verified
            if (!empty($row['photo_proof'])) {
                echo "<div class='proof-verified'>Proof Verified</div>";
            } else {
                echo "<button class='btn btn-primary btn-sm' onclick=\"verifyProof('".$row['regNo']."', '".$row['submission_datetime']."', this)\">Verify Proof</button>";
            }
            echo "</td>
                </tr>";
        }

        echo "</tbody>
            </table>
        </div>
    </div>

    <script>
        // Function to verify proof
        function verifyProof(regNo, submissionDatetime, button) {
            var fileInput = document.createElement('input');
            fileInput.setAttribute('type', 'file');
            fileInput.setAttribute('accept', 'image/*'); // Accept all image file types
            fileInput.addEventListener('change', function() {
                var file = fileInput.files[0];
                if (file) {
                    // Here you can add the logic to handle the proof verification
                    alert(\"Proof verification initiated for Registration Number: \" + regNo + \" with file: \" + file.name);
                    button.textContent = 'Proof Verified';
                    button.disabled = true;
                    // Call PHP script to handle file upload and database update
                    var xhr = new XMLHttpRequest();
                    var formData = new FormData();
                    formData.append('file', file);
                    formData.append('regNo', regNo);
                    formData.append('submissionDatetime', submissionDatetime);
                    xhr.open('POST', '".$_SERVER['PHP_SELF']."', true);
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            // Handle response from server
                            if (xhr.responseText === 'success') {
                                alert('Proof verified and updated successfully.');
                            } else {
                                // Handle other response scenarios if needed
                            }
                        }
                    };
                    xhr.send(formData);
                } else {
                    alert(\"No file selected for proof verification.\");
                }
            });
            fileInput.click();
        }
    </script>
</body>
</html>";
    } else {
        
        echo "<p>No data found for the specified registration number.</p>";
    }

    $stmt->close();
}

// Handle file upload and database update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['file']) && isset($_POST['regNo']) && isset($_POST['submissionDatetime'])) {
    $regNo = $_POST['regNo'];
    $submissionDatetime = $_POST['submissionDatetime'];
    $file = $_FILES['file'];

    // Check if file upload is successful
    if ($file['error'] === UPLOAD_ERR_OK) {
        // Convert the file into binary data
        $fileContent = file_get_contents($file['tmp_name']);

        // Update the database with the binary data
        $updateSql = "UPDATE oddata SET photo_proof = ? WHERE regNo = ? AND submission_datetime = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("sss", $fileContent, $regNo, $submissionDatetime);
        if ($stmt->execute()) {
            echo "success"; // Return success response
        } else {
            echo "error: " . $conn->error; // Return error response with specific database error
        }
        $stmt->close();
    } else {
        echo "error"; // Return error response
    }
}

// Close the database connection
$conn->close();
?>
