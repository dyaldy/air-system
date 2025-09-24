<?php
// Test script to verify batch functionality is working
require_once 'index.php';

// Test the Project_batch_model functionality
echo "<h2>Testing Project Batch Functionality</h2>";

// Check if we can create a CI instance
$CI = &get_instance();
$CI->load->model('Project_batch_model');

echo "<h3>1. Testing Model Loading</h3>";
if (isset($CI->Project_batch_model)) {
    echo "✓ Project_batch_model loaded successfully<br>";
} else {
    echo "✗ Failed to load Project_batch_model<br>";
}

// Test get_project_batches method
echo "<h3>2. Testing get_project_batches() method</h3>";
try {
    // Test with dummy data
    $test_batches = $CI->Project_batch_model->get_project_batches('Test', 1, 1);
    echo "✓ get_project_batches() method executed without errors<br>";
    echo "Result: " . (is_array($test_batches) ? count($test_batches) . " batches found" : "No results") . "<br>";
} catch (Exception $e) {
    echo "✗ Error in get_project_batches(): " . $e->getMessage() . "<br>";
}

// Test database table existence
echo "<h3>3. Testing Database Table</h3>";
try {
    $query = $CI->db->query("DESCRIBE as_project_batches");
    if ($query->num_rows() > 0) {
        echo "✓ as_project_batches table exists<br>";
        echo "Columns:<br>";
        foreach ($query->result() as $column) {
            echo "- {$column->Field} ({$column->Type})<br>";
        }
    } else {
        echo "✗ as_project_batches table not found<br>";
    }
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "<br>";
}

echo "<h3>4. Current Batch Data</h3>";
try {
    $all_batches = $CI->db->get('as_project_batches')->result();
    if (count($all_batches) > 0) {
        echo "Found " . count($all_batches) . " batches in database:<br>";
        foreach ($all_batches as $batch) {
            echo "- Batch ID: {$batch->batch_id}, Project: {$batch->project_name}, Quantity: {$batch->remaining_quantity}/{$batch->initial_quantity}<br>";
        }
    } else {
        echo "No batches found in database<br>";
    }
} catch (Exception $e) {
    echo "✗ Error retrieving batches: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "Test completed. You can now test the batch selection UI by accessing the take form.";
?>