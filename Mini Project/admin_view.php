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

// Fetch regNo and submissionDate from the URL parameters
$regNo = $_GET['regNo'];
$submissionDate = $_GET['submissionDate'];

// Fetch data for the specific user
$sql = "SELECT * FROM oddata WHERE regNo = '$regNo' AND submission_datetime = '$submissionDate'";
$result = $conn->query($sql);

// Display data in individual boxes
if ($result->num_rows > 0) {
    echo "<!DOCTYPE html>
    <html lang=\"en\">
    <head>
        <meta charset=\"UTF-8\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
        <title>User Information</title>
        <link rel=\"stylesheet\" href=\"https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css\">
        <style>
            .container {
                text-align: center;
            }
            .small-box {
                border: 1px solid #ccc;
                padding: 10px;
                margin: 5px;
                display: inline-block;
                width: calc(33.33% - 10px); /* Three boxes in a row with some margin */
                vertical-align: top; /* Align boxes to the top */
            }
            .photo-box {
                margin-top: 20px;
            }
            .photo {
                display: none;
                width: 30%; /* Adjust the width of the photo as needed */
                margin: 0 auto;
            }
        </style>
    </head>
    <body>
        <div class=\"container mt-5\">";

    while($row = $result->fetch_assoc()) {
        echo "<div class=\"small-box\">
                <p><strong>Name:</strong> ".$row['name']."</p>
              </div>";
        echo "<div class=\"small-box\">
                <p><strong>Reg No:</strong> ".$row['regNo']."</p>
              </div>";
        echo "<div class=\"small-box\">
                <p><strong>Start Date:</strong> ".$row['startDate']."</p>
              </div>";
        echo "<div class=\"small-box\">
                <p><strong>End Date:</strong> ".$row['endDate']."</p>
              </div>";
        echo "<div class=\"small-box\">
                <p><strong>Purpose:</strong> ".$row['purpose']."</p>
              </div>";
        echo "<div class=\"small-box\">
                <p><strong>Place of Visit:</strong> ".$row['place']."</p>
              </div>";
        echo "<div class=\"small-box\">
                <p><strong>Status:</strong> ".$row['status']."</p>
              </div>";
        echo "<div class=\"small-box\">
                <p><strong>Submission Date:</strong> ".$row['submission_datetime']."</p>
              </div>";

        // Display additional box for permissionStaff if odType is 'permission'
        if ($row['odType'] === 'permission') {
            if ($row['permissionStaff'] !== null) {
                echo "<div class=\"small-box\">
                        <p><strong>Permission Staff:</strong> ".$row['permissionStaff']."</p>
                      </div>";
            } else {
                echo "<div class=\"small-box\">
                        <p><strong>OD Type:</strong> Full OD</p>
                      </div>";
            }
        } elseif ($row['odType'] === 'full') {
            echo "<div class=\"small-box\">
                    <p><strong>OD Type:</strong> Full OD</p>
                  </div>";
        }

        // Display the photo in its own box at the end with a button to show/hide it
        echo "<div class=\"photo-box\">
                <button class=\"btn btn-primary\" onclick=\"togglePhoto(this)\">Open Photo</button>
                <p><strong>Photo Proof</strong></p>
                <img src=\"data:image/jpeg;base64,".base64_encode($row['photo_proof'])."\" class=\"photo\" />
              </div>";
    }

    echo "</div>
    <script>
        function togglePhoto(button) {
            var photo = button.nextElementSibling.nextElementSibling; // Get the photo element
            if (photo.style.display === 'none') {
                photo.style.display = 'block';
                button.textContent = 'Close Photo';
            } else {
                photo.style.display = 'none';
                button.textContent = 'Open Photo';
            }
        }
    </script>
    </body>
</html>";
} else {
    echo "<p>No data found for this user.</p>";
}

// Close the database connection
$conn->close();
?>
