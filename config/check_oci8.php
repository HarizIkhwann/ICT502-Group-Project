<?php
echo "<h2>PHP OCI8 Extension Check</h2>";

if (function_exists('oci_connect')) {
    echo "✅ <strong>OCI8 extension is installed</strong><br>";
    echo "PHP Version: " . phpversion() . "<br>";
    if (function_exists('oci_client_version')) {
        echo "Oracle Client Version: " . oci_client_version();
    }
} else {
    echo "❌ <strong>OCI8 extension is NOT installed</strong><br><br>";
    echo "<strong>Steps to fix:</strong><br>";
    echo "1. Download Oracle Instant Client from Oracle website<br>";
    echo "2. Extract to C:\\oracle\\instantclient_23_6 (or similar)<br>";
    echo "3. Add Oracle path to Windows System Environment PATH<br>";
    echo "4. Edit php.ini and add/uncomment: extension=oci8_19<br>";
    echo "5. Restart Laragon/Apache<br><br>";
    echo "PHP Version: " . phpversion() . "<br>";
    echo "Loaded Extensions: " . implode(", ", get_loaded_extensions());
}
