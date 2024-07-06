// Get the table element
var table = document.getElementById("studentsTable");

// Get the total number of rows in the table
var rowCount = table.rows.length;

// Start the serial number from 1
var serialNumber = 1;

// Loop through each row and update the serial number
for (var i = 1; i < rowCount; i++) {
    // Create a new cell for the status column
    var statusCell = table.rows[i].insertCell(-1);
    
    // Create approve and reject buttons inside the status cell
    var approveButton = document.createElement("button");
    approveButton.textContent = "Approve";
    approveButton.onclick = function() {
        // Logic for approving the student
        alert("Student approved.");
    };
    
    var rejectButton = document.createElement("button");
    rejectButton.textContent = "Reject";
    rejectButton.onclick = function() {
        // Logic for rejecting the student
        alert("Student rejected.");
    };
    
    // Append the buttons to the status cell
    statusCell.appendChild(approveButton);
    statusCell.appendChild(rejectButton);
}
