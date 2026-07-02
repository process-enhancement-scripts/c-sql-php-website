<?php

// Database connection
try {
    $db = new PDO('mysql:host=localhost;dbname=anime_tier_db', 'root', 'root');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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

// Display unranked characters
$stmtUnranked = $db->prepare("
    SELECT character_id, name
    FROM characters
    WHERE character_id NOT IN (SELECT character_id FROM tier_assignments)
    ORDER BY name
");
$stmtUnranked->execute();
$unranked = $stmtUnranked->fetchAll(PDO::FETCH_ASSOC);

if (!empty($unranked)) {
    echo "<h2>Unranked Characters</h2>\n";
    echo "<ul>\n";
    foreach ($unranked as $character) {
        echo "    <li>" . htmlspecialchars($character['name']) . " <em>(not yet ranked)</em></li>\n";
    }
    echo "</ul>\n";
}

?>
