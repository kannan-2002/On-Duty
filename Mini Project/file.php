<?php
// Assuming you have received the image path from your database or any other source
$image_path = 'path/to/your/image.jpg'; // Replace this with the actual image path
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Open Image Example</title>
</head>
<body>
    <!-- Button to trigger image opening -->
    <button onclick="openImage('<?php echo $image_path; ?>')">Open Image</button>

    <!-- Image element to display the image -->
    <img id="imageDisplay" src="" alt="Image" style="display: none; max-width: 100%;">

    <!-- Script to handle image opening -->
    <script>
        function openImage(imagePath) {
            // Update the source of the image element with the image path
            var imageElement = document.getElementById('imageDisplay');
            imageElement.src = imagePath;
            imageElement.style.display = 'block'; // Show the image element
        }
    </script>
</body>
</html>
