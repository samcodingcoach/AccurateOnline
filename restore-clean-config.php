<?php
/**
 * Restore config.php yang bersih dari folder bak
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clean_scope'])) {
    $cleanScope = trim($_POST['clean_scope']);
    
    // Validasi scope string
    if (empty($cleanScope)) {
        die('Error: Clean scope string is empty');
    }
    
    // Path ke config files
    $currentConfigPath = __DIR__ . '/config/config.php';
    $bakConfigPath = __DIR__ . '/bak/config/config.php';
    
    if (!file_exists($currentConfigPath)) {
        die('Error: Current config file not found');
    }
    
    if (!file_exists($bakConfigPath)) {
        die('Error: BAK config file not found');
    }
    
    // Baca current config file
    $currentConfigContent = file_get_contents($currentConfigPath);
    
    if ($currentConfigContent === false) {
        die('Error: Cannot read current config file');
    }
    
    // Backup current config file
    $backupPath = $currentConfigPath . '.backup.' . date('Y-m-d_H-i-s');
    file_put_contents($backupPath, $currentConfigContent);
    
    // Baca BAK config untuk mendapatkan semua nilai yang bersih
    $bakConfigContent = file_get_contents($bakConfigPath);
    
    if ($bakConfigContent === false) {
        die('Error: Cannot read BAK config file');
    }
    
    // Extract values dari BAK config
    $bakValues = [];
    
    // Extract semua define statements dari BAK
    if (preg_match_all("/define\('([^']+)', '([^']*)'/", $bakConfigContent, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $bakValues[$match[1]] = $match[2];
        }
    }
    
    // Update current config dengan nilai dari BAK (kecuali yang spesifik untuk environment saat ini)
    $newConfigContent = $currentConfigContent;
    
    // Daftar konstanta yang akan di-restore dari BAK
    $constantsToRestore = [
        'ACCURATE_TOKEN_SCOPE'
    ];
    
    foreach ($constantsToRestore as $constant) {
        if (isset($bakValues[$constant])) {
            $pattern = "/define\('" . preg_quote($constant) . "', '[^']*'\);/";
            $replacement = "define('" . $constant . "', '" . addslashes($bakValues[$constant]) . "');";
            $newConfigContent = preg_replace($pattern, $replacement, $newConfigContent);
        }
    }
    
    // Tulis config file yang baru
    $writeResult = file_put_contents($currentConfigPath, $newConfigContent);
    
    if ($writeResult === false) {
        die('Error: Failed to write config file');
    }
    
    echo "<h2>Config Restore Success</h2>";
    echo "<p style='color: green;'>✓ Config file restored successfully from BAK!</p>";
    echo "<p><strong>Backup created:</strong> " . htmlspecialchars(basename($backupPath)) . "</p>";
    
    echo "<h3>Restored Values:</h3>";
    foreach ($constantsToRestore as $constant) {
        if (isset($bakValues[$constant])) {
            echo "<p><strong>" . $constant . ":</strong></p>";
            echo "<textarea rows='2' cols='80' readonly>" . htmlspecialchars($bakValues[$constant]) . "</textarea>";
        }
    }
    
    echo "<h3>Comparison:</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Aspect</th><th>Before (Problematic)</th><th>After (Clean)</th></tr>";
    
    // Compare scope counts
    $oldScope = '';
    if (preg_match("/define\('ACCURATE_TOKEN_SCOPE', '([^']*)'/", $currentConfigContent, $matches)) {
        $oldScope = $matches[1];
    }
    
    $oldScopes = !empty($oldScope) ? explode(' ', trim($oldScope)) : [];
    $newScopes = !empty($bakValues['ACCURATE_TOKEN_SCOPE']) ? explode(' ', trim($bakValues['ACCURATE_TOKEN_SCOPE'])) : [];
    
    echo "<tr>";
    echo "<td>Scope Count</td>";
    echo "<td>" . count($oldScopes) . " scopes</td>";
    echo "<td>" . count($newScopes) . " scopes</td>";
    echo "</tr>";
    
    // Show removed problematic scopes
    $problematicScopes = ['item_brand_view', 'job_order_view'];
    $removedProblematic = array_intersect($oldScopes, $problematicScopes);
    
    echo "<tr>";
    echo "<td>Problematic Scopes Removed</td>";
    echo "<td style='color: red;'>" . implode(', ', $removedProblematic) . "</td>";
    echo "<td style='color: green;'>None (Clean)</td>";
    echo "</tr>";
    
    echo "</table>";
    
    echo "<h3>Next Steps:</h3>";
    echo "<ul>";
    echo "<li><a href='oauth/authorize.php'>Go to Authorize page</a> to verify the fix</li>";
    echo "<li><a href='compare-scope-analysis.php'>Run comparison analysis again</a> to verify</li>";
    echo "</ul>";
    
} else {
    echo "<h2>Error</h2>";
    echo "<p style='color: red;'>Invalid request. Please use the form from compare-scope-analysis.php</p>";
    echo "<p><a href='compare-scope-analysis.php'>← Back to Scope Analysis</a></p>";
}
?>