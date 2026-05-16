<?php
$conn = new mysqli('localhost', 'root', '', 'dream_website');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully\n";
$res = $conn->query("SELECT * FROM users");
if (!$res) {
    echo "Query failed: " . $conn->error;
} else {
    echo "Found " . $res->num_rows . " users:\n";
    while ($row = $res->fetch_assoc()) {
        print_r($row);
    }
}
$conn->close();
?>
