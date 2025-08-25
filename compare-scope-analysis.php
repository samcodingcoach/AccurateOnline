<?php
/**
 * Analisis perbandingan scope antara folder bak (bersih) dan current (bermasalah)
 */

echo "<h2>Scope Comparison Analysis</h2>";

// Load config dari folder bak (bersih)
echo "<h3>1. Config dari Folder BAK (Bersih):</h3>";
$bakConfigPath = __DIR__ . '/bak/config/config.php';
if (file_exists($bakConfigPath)) {
    $bakConfigContent = file_get_contents($bakConfigPath);
    
    // Extract ACCURATE_TOKEN_SCOPE dari bak
    if (preg_match("/define\('ACCURATE_TOKEN_SCOPE', '([^']*)'/", $bakConfigContent, $matches)) {
        $bakScope = $matches[1];
        echo "<p><strong>BAK Scope:</strong></p>";
        echo "<textarea rows='3' cols='80' readonly>" . htmlspecialchars($bakScope) . "</textarea>";
        
        $bakScopes = !empty($bakScope) ? explode(' ', trim($bakScope)) : [];
        echo "<p><strong>BAK Scopes Count:</strong> " . count($bakScopes) . "</p>";
        echo "<p><strong>BAK Scopes List:</strong></p>";
        echo "<ul>";
        foreach ($bakScopes as $scope) {
            echo "<li>" . htmlspecialchars($scope) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>Could not extract scope from BAK config</p>";
        $bakScopes = [];
    }
} else {
    echo "<p style='color: red;'>BAK config file not found</p>";
    $bakScopes = [];
}

// Load config current
echo "<h3>2. Config Current (Bermasalah):</h3>";
require_once __DIR__ . '/config/config.php';
$currentScope = defined('ACCURATE_TOKEN_SCOPE') ? ACCURATE_TOKEN_SCOPE : '';
echo "<p><strong>Current Scope:</strong></p>";
echo "<textarea rows='3' cols='80' readonly>" . htmlspecialchars($currentScope) . "</textarea>";

$currentScopes = !empty($currentScope) ? explode(' ', trim($currentScope)) : [];
echo "<p><strong>Current Scopes Count:</strong> " . count($currentScopes) . "</p>";
echo "<p><strong>Current Scopes List:</strong></p>";
echo "<ul>";
foreach ($currentScopes as $scope) {
    echo "<li>" . htmlspecialchars($scope) . "</li>";
}
echo "</ul>";

// Comparison analysis
echo "<h3>3. Comparison Analysis:</h3>";

if (!empty($bakScopes) && !empty($currentScopes)) {
    // Scope yang ditambahkan (ada di current tapi tidak di bak)
    $addedScopes = array_diff($currentScopes, $bakScopes);
    
    // Scope yang dihapus (ada di bak tapi tidak di current)
    $removedScopes = array_diff($bakScopes, $currentScopes);
    
    // Scope yang sama
    $sameScopes = array_intersect($bakScopes, $currentScopes);
    
    echo "<h4>Added Scopes (Potentially Problematic):</h4>";
    if (!empty($addedScopes)) {
        echo "<ul style='color: red;'>";
        foreach ($addedScopes as $scope) {
            echo "<li><strong>" . htmlspecialchars($scope) . "</strong></li>";
        }
        echo "</ul>";
        echo "<p><strong>Total added:</strong> " . count($addedScopes) . "</p>";
    } else {
        echo "<p style='color: green;'>No scopes added</p>";
    }
    
    echo "<h4>Removed Scopes:</h4>";
    if (!empty($removedScopes)) {
        echo "<ul style='color: orange;'>";
        foreach ($removedScopes as $scope) {
            echo "<li>" . htmlspecialchars($scope) . "</li>";
        }
        echo "</ul>";
        echo "<p><strong>Total removed:</strong> " . count($removedScopes) . "</p>";
    } else {
        echo "<p style='color: green;'>No scopes removed</p>";
    }
    
    echo "<h4>Same Scopes:</h4>";
    echo "<p><strong>Total same:</strong> " . count($sameScopes) . "</p>";
    
    // Identify problematic scopes
    $knownProblematicScopes = ['item_brand_view', 'job_order_view'];
    $foundProblematic = array_intersect($addedScopes, $knownProblematicScopes);
    
    if (!empty($foundProblematic)) {
        echo "<h4 style='color: red;'>⚠️ Confirmed Problematic Scopes Found:</h4>";
        echo "<ul style='color: red; font-weight: bold;'>";
        foreach ($foundProblematic as $scope) {
            echo "<li>" . htmlspecialchars($scope) . " (Known to cause issues)</li>";
        }
        echo "</ul>";
    }
    
} else {
    echo "<p style='color: orange;'>Cannot perform comparison - missing data</p>";
}

// Recommendation
echo "<h3>4. Recommendation:</h3>";
if (!empty($addedScopes)) {
    echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px;'>";
    echo "<h4>🔧 Recommended Action:</h4>";
    echo "<p>Restore config.php to the clean version from BAK folder to fix scope issues.</p>";
    
    echo "<form method='post' action='restore-clean-config.php'>";
    echo "<input type='hidden' name='clean_scope' value='" . htmlspecialchars($bakScope ?? '') . "'>";
    echo "<button type='submit' style='background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;'>Restore Clean Config from BAK</button>";
    echo "</form>";
    echo "</div>";
} else {
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px;'>";
    echo "<h4>✅ Status:</h4>";
    echo "<p>Config appears to be clean already.</p>";
    echo "</div>";
}

// File modification times
echo "<h3>5. File Modification Times:</h3>";
$currentConfigPath = __DIR__ . '/config/config.php';

if (file_exists($bakConfigPath)) {
    $bakModTime = filemtime($bakConfigPath);
    echo "<p><strong>BAK config modified:</strong> " . date('Y-m-d H:i:s', $bakModTime) . "</p>";
}

if (file_exists($currentConfigPath)) {
    $currentModTime = filemtime($currentConfigPath);
    echo "<p><strong>Current config modified:</strong> " . date('Y-m-d H:i:s', $currentModTime) . "</p>";
    
    if (isset($bakModTime)) {
        $timeDiff = $currentModTime - $bakModTime;
        $daysDiff = round($timeDiff / (60 * 60 * 24), 1);
        echo "<p><strong>Time difference:</strong> " . $daysDiff . " days (current is newer)</p>";
    }
}

echo "<p><a href='oauth/authorize.php'>← Back to Authorize</a></p>";
?>