<?php
// Debug script to check admin table structure and data
include 'database/connection.php';

echo "<h3>Admin Table Debug</h3>";

// Check if admin table exists
$check_table = "SHOW TABLES LIKE 'admin'";
$result = mysqli_query($connect, $check_table);
if (mysqli_num_rows($result) > 0) {
    echo "✓ Admin table exists<br>";
    
    // Check table structure
    echo "<h4>Admin Table Structure:</h4>";
    $describe = "DESCRIBE admin";
    $structure = mysqli_query($connect, $describe);
    if ($structure) {
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = mysqli_fetch_assoc($structure)) {
            echo "<tr>";
            echo "<td>{$row['Field']}</td>";
            echo "<td>{$row['Type']}</td>";
            echo "<td>{$row['Null']}</td>";
            echo "<td>{$row['Key']}</td>";
            echo "<td>{$row['Default']}</td>";
            echo "<td>{$row['Extra']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Check if there are any admins
    echo "<h4>Admin Data:</h4>";
    $count_query = "SELECT COUNT(*) as count FROM admin";
    $count_result = mysqli_query($connect, $count_query);
    if ($count_result) {
        $count = mysqli_fetch_assoc($count_result)['count'];
        echo "Number of admins: $count<br>";
        
        if ($count > 0) {
            // Show sample data
            $sample_query = "SELECT admin_id, fname, lname, email, created_at FROM admin LIMIT 3";
            $sample_result = mysqli_query($connect, $sample_query);
            if ($sample_result) {
                echo "<table border='1'>";
                echo "<tr><th>Admin ID</th><th>First Name</th><th>Last Name</th><th>Email</th><th>Created</th></tr>";
                while ($row = mysqli_fetch_assoc($sample_result)) {
                    echo "<tr>";
                    echo "<td>{$row['admin_id']}</td>";
                    echo "<td>{$row['fname']}</td>";
                    echo "<td>{$row['lname']}</td>";
                    echo "<td>{$row['email']}</td>";
                    echo "<td>{$row['created_at']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "Error fetching sample data: " . mysqli_error($connect);
            }
        }
    }
    
} else {
    echo "❌ Admin table does not exist<br>";
}

// Test the exact query from show_admins.php
echo "<h4>Testing show_admins.php Query:</h4>";
$test_query = "SELECT admin_id, fname, lname, email, created_at FROM admin ORDER BY created_at DESC";
$test_result = mysqli_query($connect, $test_query);
if ($test_result) {
    echo "✓ Query executed successfully<br>";
    echo "Number of rows returned: " . mysqli_num_rows($test_result) . "<br>";
} else {
    echo "❌ Query failed: " . mysqli_error($connect) . "<br>";
}

$connect->close();
?>
