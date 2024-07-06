// server.js
const express = require('express');
const bodyParser = require('body-parser');

const app = express();
const PORT = 3000;

app.use(bodyParser.json());

// In-memory data store for simplicity (replace with a database in a real-world scenario)
let applicationStatus = {
    status: 'Pending',
    photo: null,
};

app.post('/submit', (req, res) => {
    const { details, date, reason } = req.body;

    // Perform staff confirmation logic here
    // For simplicity, we'll just update the status to 'Approved' immediately
    applicationStatus.status = 'Approved';

    res.json({ message: 'Application submitted and status updated.' });
});

app.get('/status', (req, res) => {
    res.json({ status: applicationStatus.status });
});

app.listen(PORT, () => {
    console.log(`Server is running on http://localhost:${PORT}`);
});
