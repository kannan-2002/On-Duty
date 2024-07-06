// script.js

function submitForm() {
    const details = document.getElementById('details').value;
    const date = document.getElementById('date').value;
    const reason = document.getElementById('reason').value;

    // Send data to the server (backend) using AJAX or fetch
    // Example: Use fetch to send data to the server
    fetch('/submit', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ details, date, reason }),
    })
    .then(response => response.json())
    .then(data => {
        alert('Application submitted successfully!');
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function checkStatus() {
    // Fetch status from the server and update the status element
    // Example: Use fetch to get status from the server
    fetch('/status')
    .then(response => response.json())
    .then(data => {
        document.getElementById('status').innerHTML = `Status: ${data.status}`;
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
