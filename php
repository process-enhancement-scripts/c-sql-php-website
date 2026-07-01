%%writefile display_tier_list.php
<?php

// Database connection (adjust credentials and database name as needed)
// For demonstration, using a placeholder database name 'anime_tier_db'
// In a real application, you'd manage credentials securely.
try {
    $db = new PDO('mysql:host=localhost;dbname=anime_tier_db', 'root', 'root'); // Changed password to 'root'
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>Database connection successful!</p>\n";
} catch (PDOException $e) {
    die("Error connecting to database: " . $e->getMessage());
}

// Fetch all tier assignments and character names
$stmt = $db->prepare("
    SELECT c.name, ta.tier_rank
    FROM characters c
    JOIN tier_assignments ta ON c.character_id = ta.character_id
    ORDER BY FIELD(ta.tier_rank, 'S', 'A', 'B', 'C', 'D', 'E', 'F'), c.name
");
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group characters by tier
$tierList = [];
foreach ($results as $row) {
    $tierList[$row['tier_rank']][] = $row['name'];
}

// Define the tier order
$tierOrder = ['S', 'A', 'B', 'C', 'D', 'E', 'F'];

echo "<h1>Anime Character Tier List</h1>\n";

if (empty($tierList)) {
    echo "<p>No characters have been ranked yet!</p>\n";
} else {
    foreach ($tierOrder as $tier) {
        if (isset($tierList[$tier])) {
            echo "<h2>" . $tier . " Tier</h2>\n";
            echo "<ul>\n";
            foreach ($tierList[$tier] as $characterName) {
                echo "    <li>" . htmlspecialchars($characterName) . "</li>\n";
            }
            echo "</ul>\n";
        }
    }
}

// You could also display unranked characters if you had that logic

?>
