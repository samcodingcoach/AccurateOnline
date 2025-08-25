<?php
/**
 * Update config.php dengan scope yang benar dari API
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_scope'])) {
    $newScopeString = trim($_POST['new_scope']);
    
    // Validasi scope string
    if (empty($newScopeString)) {
        die('Error: New scope string is empty');
    }
    
    // Path ke config file
    $configPath = __DIR__ . '/config/config.php';
    
    if (!file_exists($configPath)) {
        die('Error: Config file not found');
    }
    
    // Baca config file
    $configContent = file_get_contents($configPath);
    
    if ($configContent === false) {
        die('Error: Cannot read config file');
    }
    
    // Backup config file
    $backupPath = $configPath . '.backup.' . date('Y-m-d_H-i-s');
    file_put_contents($backupPath, $configContent);
    
    // Update ACCURATE_TOKEN_SCOPE
    $pattern = "/define\('ACCURATE_TOKEN_SCOPE', '[^']*'\);/";
    $replacement = "define('ACCURATE_TOKEN_SCOPE', '" . addslashes($newScopeString) . "');";
    
    $newConfigContent = preg_replace($pattern, $replacement, $configContent);
    
    if ($newConfigContent === null) {
        die('Error: Failed to update config content');
    }
    
    // Tulis config file yang baru
    $writeResult = file_put_contents($configPath, $newConfigContent);
    
    if ($writeResult === false) {
        die('Error: Failed to write config file');
    }
    
    echo "<h2>Config Update Success</h2>";
    echo "<p style='color: green;'>✓ Config file updated successfully!</p>";
    echo "<p><strong>Backup created:</strong> " . htmlspecialchars(basename($backupPath)) . "</p>";
    echo "<p><strong>New scope string:</strong></p>";
    echo "<textarea rows='3' cols='80' readonly>" . htmlspecialchars($newScopeString) . "</textarea>";
    
    echo "<h3>Next Steps:</h3>";
    echo "<ul>";
    echo "<li><a href='oauth/authorize.php'>Go to Authorize page</a> to verify the fix</li>";
    echo "<li><a href='test-scope-cleanup.php'>Run scope test again</a> to verify</li>";
    echo "</ul>";
    
} else {
    echo "<h2>Error</h2>";
    echo "<p style='color: red;'>Invalid request. Please use the form from test-scope-cleanup.php</p>";
    echo "<p><a href='test-scope-cleanup.php'>← Back to Scope Test</a></p>";
}
?>