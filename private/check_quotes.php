<?php
/**
 * Quick script to check for leads in quote_requests table
 */
require_once __DIR__ . '/includes/db.php';

try {
    // Count total leads
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM quote_requests");
    $count = $stmt->fetch();
    $total = $count['total'] ?? 0;
    
    echo "Total leads in quote_requests table: " . $total . "\n\n";
    
    if ($total > 0) {
        // Get all leads
        $stmt = $pdo->query("SELECT id, name, email, phone, event_type, event_date, guest_count, location, submitted_at FROM quote_requests ORDER BY submitted_at DESC");
        $leads = $stmt->fetchAll();
        
        echo "Leads:\n";
        echo str_repeat("=", 80) . "\n";
        foreach ($leads as $lead) {
            echo "ID: " . $lead['id'] . "\n";
            echo "Name: " . $lead['name'] . "\n";
            echo "Email: " . $lead['email'] . "\n";
            echo "Phone: " . ($lead['phone'] ?: 'N/A') . "\n";
            echo "Event Type: " . ($lead['event_type'] ?: 'N/A') . "\n";
            echo "Event Date: " . ($lead['event_date'] ?: 'N/A') . "\n";
            echo "Guest Count: " . ($lead['guest_count'] ?: 'N/A') . "\n";
            echo "Location: " . ($lead['location'] ?: 'N/A') . "\n";
            echo "Submitted: " . $lead['submitted_at'] . "\n";
            echo str_repeat("-", 80) . "\n";
        }
    } else {
        echo "No leads found in the quote_requests table.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>

