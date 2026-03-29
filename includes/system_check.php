<?php
/**
 * System Verification & Testing Script
 * Run this to verify the Custom Tour Package System is properly set up
 */

session_start();
include("db.php");

$checks = [];
$all_passed = true;

echo "<!DOCTYPE html>
<html>
<head>
    <title>Custom Package System - Verification</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto; }
        .pass { color: green; margin: 10px 0; }
        .fail { color: red; margin: 10px 0; }
        .warning { color: orange; margin: 10px 0; }
        h2 { color: #333; border-bottom: 2px solid #0d6efd; padding-bottom: 10px; }
        .section { margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #0d6efd; color: white; padding: 10px; text-align: left; }
        td { border-bottom: 1px solid #ddd; padding: 8px; }
        tr:hover { background: #f9f9f9; }
        .status-icon { font-size: 20px; }
    </style>
</head>
<body>
<div class='container'>
<h2>🔧 Custom Tour Package System - Verification</h2>";

// =====================
// 1. DATABASE CONNECTION
// =====================
echo "<div class='section'><h3>1️⃣ Database Connection</h3>";

if($conn->connect_error) {
    echo "<p class='fail'>❌ Database Connection Failed: " . htmlspecialchars($conn->connect_error) . "</p>";
    $all_passed = false;
} else {
    echo "<p class='pass'>✅ Database Connected Successfully</p>";
    echo "<p><b>Database:</b> tour_travel</p>";
    echo "<p><b>Host:</b> localhost</p>";
}
echo "</div>";

// =====================
// 2. TABLE STRUCTURE
// =====================
echo "<div class='section'><h3>2️⃣ Database Table Structure</h3>";

$required_columns = [
    'id' => 'Table ID',
    'user_id' => 'User Reference',
    'service_type' => 'Service Type (REQUIRED)',
    'pickup_location' => 'Pickup Location (REQUIRED)',
    'destinations' => 'Destinations (REQUIRED)',
    'sightseeing_places' => 'Sightseeing Places (REQUIRED)',
    'travel_date' => 'Travel Date',
    'days' => 'Days',
    'travelers' => 'Travelers',
    'hotel_type' => 'Hotel Type (REQUIRED)',
    'user_notes' => 'User Notes (REQUIRED)',
    'car_type' => 'Car Type (REQUIRED)',
    'status' => 'Status (REQUIRED)',
    'created_at' => 'Created Date (REQUIRED)'
];

$table_check = $conn->query("SHOW TABLES LIKE 'custom_package_requests'");

if($table_check->num_rows == 0) {
    echo "<p class='fail'>❌ Table 'custom_package_requests' NOT FOUND!</p>";
    echo "<p class='warning'>⚠️ Run the database migration first!</p>";
    $all_passed = false;
} else {
    echo "<p class='pass'>✅ Table 'custom_package_requests' Found</p>";
    
    // Check columns
    echo "<table>
    <thead>
        <tr>
            <th>Column Name</th>
            <th>Type</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>";
    
    $structure = $conn->query("DESCRIBE custom_package_requests");
    $existing_cols = [];
    
    while($row = $structure->fetch_assoc()) {
        $existing_cols[$row['Field']] = $row['Type'];
        
        if(array_key_exists($row['Field'], $required_columns)) {
            $label = $required_columns[$row['Field']];
            $is_required = strpos($label, 'REQUIRED') !== false;
            $status = $is_required ? '✅ Required' : '✅ OK';
            echo "<tr>
                <td><b>{$row['Field']}</b> - {$label}</td>
                <td>{$row['Type']}</td>
                <td class='pass'>{$status}</td>
            </tr>";
        }
    }
    
    echo "</tbody>
    </table>";
    
    // Check for missing columns
    echo "<h4 style='margin-top: 20px;'>Missing Columns:</h4>";
    $missing = [];
    foreach($required_columns as $col => $label) {
        if(strpos($label, 'REQUIRED') !== false && !isset($existing_cols[$col])) {
            $missing[] = $col;
            echo "<p class='fail'>❌ Missing: <b>$col</b> - $label</p>";
            $all_passed = false;
        }
    }
    
    if(empty($missing)) {
        echo "<p class='pass'>✅ All required columns present!</p>";
    } else {
        echo "<p class='warning'>⚠️ Run the database migration to add missing columns</p>";
    }
}
echo "</div>";

// =====================
// 3. FILE STRUCTURE
// =====================
echo "<div class='section'><h3>3️⃣ File Structure & Permissions</h3>";

$files_to_check = [
    'index.php' => 'Homepage with service selection',
    'user/custom-package.php' => 'Custom package form',
    'user/submit-custom-package.php' => 'Form submission handler',
    'admin/custom-package-requests.php' => 'Admin view requests',
    'includes/db_migration.php' => 'Database migration script',
    'includes/schema_update.sql' => 'SQL schema file'
];

$base_path = dirname(dirname(__FILE__));

foreach($files_to_check as $file => $description) {
    $full_path = $base_path . '/' . $file;
    if(file_exists($full_path)) {
        $size = filesize($full_path);
        echo "<p class='pass'>✅ <b>$file</b> - {$description} ({$size} bytes)</p>";
    } else {
        echo "<p class='fail'>❌ <b>$file</b> - FILE NOT FOUND!</p>";
        $all_passed = false;
    }
}

echo "</div>";

// =====================
// 4. FORM FUNCTIONALITY
// =====================
echo "<div class='section'><h3>4️⃣ Form Functionality Test</h3>";

$form_test = true;

if(!isset($_SESSION['user_id'])) {
    echo "<p class='warning'>⚠️ Not logged in as user. Some tests skipped.</p>";
    echo "<p>Log in as a regular user to see form preview</p>";
    $form_test = false;
}

if($form_test) {
    echo "<p class='pass'>✅ User session active</p>";
} else {
    echo "<p class='warning'>⚠️ User session not available</p>";
}

echo "</div>";

// =====================
// 5. ADMIN FUNCTIONALITY
// =====================
echo "<div class='section'><h3>5️⃣ Admin Panel Status</h3>";

$admin_check = $conn->query("SELECT COUNT(*) as count FROM custom_package_requests");
if($admin_check) {
    $row = $admin_check->fetch_assoc();
    $count = $row['count'];
    
    if(isset($_SESSION['admin_id'])) {
        echo "<p class='pass'>✅ Admin logged in</p>";
    } else {
        echo "<p class='warning'>⚠️ Not logged in as admin - Log in to full test</p>";
    }
    
    echo "<p>Total custom package requests: <b>$count</b></p>";
    
    if($count > 0) {
        echo "<table>
        <thead>
            <tr>
                <th>Request ID</th>
                <th>Service Type</th>
                <th>Travelers</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>";
        
        $reqs = $conn->query("SELECT id, service_type, travelers, status FROM custom_package_requests ORDER BY id DESC LIMIT 5");
        while($req = $reqs->fetch_assoc()) {
            $type_emoji = [
                'full' => '🚗',
                'stay' => '🏨',
                'sightseeing' => '🏔️'
            ];
            $emoji = isset($type_emoji[$req['service_type']]) ? $type_emoji[$req['service_type']] : '❓';
            
            echo "<tr>
                <td>#{$req['id']}</td>
                <td>{$emoji} {$req['service_type']}</td>
                <td>{$req['travelers']}</td>
                <td>{$req['status']}</td>
            </tr>";
        }
        
        echo "</tbody>
        </table>";
    }
} else {
    echo "<p class='fail'>❌ Could not query requests table</p>";
    $all_passed = false;
}

echo "</div>";

// =====================
// SUMMARY
// =====================
echo "<div class='section' style='background: #f0f0f0; padding: 15px; border-radius: 5px;'>";

if($all_passed) {
    echo "<h3 style='color: green;'>✅ All Systems Operational!</h3>";
    echo "<p>The Custom Tour Package System is ready to use.</p>";
    echo "<p><a href='../index.php#custom-packages' style='text-decoration: none; color: #0d6efd; font-weight: bold;'>→ View Homepage</a></p>";
} else {
    echo "<h3 style='color: red;'>❌ Some checks failed</h3>";
    echo '<p style=\"color: orange;\">⚠️ <b>Critical Actions Needed:</b></p>';
    echo '<ol>';
    echo '<li>Run the database migration: <a href=\"db_migration.php\" target=\"_blank\">db_migration.php</a></li>';
    echo '<li>Or use SQL file: <a href=\"schema_update.sql\" target=\"_blank\">schema_update.sql</a></li>';
    echo '<li>After migration, come back to this page and refresh</li>';
    echo '</ol>';
}

echo "</div>";

echo "<hr style='margin-top: 30px;'>
<p style='font-size: 12px; color: #666;'>
    <b>System Version:</b> Custom Tour Package System v1.0<br>
    <b>Last Updated:</b> " . date('Y-m-d H:i:s') . "<br>
    <b>For security:</b> Delete this file (db_migration.php and this file) after setup is complete.
</p>
</div>
</body>
</html>";

$conn->close();
?>
