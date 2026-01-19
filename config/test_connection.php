<?php
require_once 'database.php';

echo "<h2>Oracle Database Connection Test</h2>";

// Test connection
$conn = getDBConnection();

if (!$conn) {
    $e = oci_error();
    echo "❌ <strong>Connection failed:</strong> " . htmlspecialchars($e['message']) . "<br><br>";
    echo "<strong>Troubleshooting:</strong><br>";
    echo "1. Check if Oracle database is running<br>";
    echo "2. Verify credentials (username: " . DB_USERNAME . ")<br>";
    echo "3. Verify service name: " . DB_SERVICE_NAME . "<br>";
    echo "4. Check if OCI8 extension is enabled in PHP<br>";
} else {
    echo "✅ <strong>Connection successful!</strong><br><br>";
    
    // Get Oracle version
    $query = "SELECT BANNER FROM v\$version WHERE ROWNUM = 1";
    $stid = oci_parse($conn, $query);
    
    if (oci_execute($stid)) {
        $row = oci_fetch_array($stid, OCI_ASSOC);
        if ($row) {
            echo "<strong>Database Version:</strong> " . htmlspecialchars($row['BANNER']) . "<br>";
        }
    }
    
    // Get current user
    $query = "SELECT USER FROM DUAL";
    $stid = oci_parse($conn, $query);
    
    if (oci_execute($stid)) {
        $row = oci_fetch_array($stid, OCI_ASSOC);
        if ($row) {
            echo "<strong>Connected as:</strong> " . htmlspecialchars($row['USER']) . "<br>";
        }
    }
    
    oci_close($conn);
    echo "<br>✅ <strong>Connection closed successfully!</strong>";
}
