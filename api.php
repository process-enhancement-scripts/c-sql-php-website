<?php

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON response header
header('Content-Type: application/json');

// Database connection
try {
    $db = new PDO('mysql:host=localhost;dbname=anime_tier_db', 'root', 'root');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit;
}

// Handle different actions
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'get_tier_list':
        getTierList();
        break;
    case 'assign_tier':
        assignTier();
        break;
    case 'remove_tier':
        removeTier();
        break;
    case 'reset_all':
        resetAll();
        break;
    default:
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ]);
}

function getTierList() {
    global $db;
    
    try {
        // Fetch all characters
        $stmtChars = $db->prepare("SELECT character_id as id, name FROM characters ORDER BY name");
        $stmtChars->execute();
        $characters = $stmtChars->fetchAll(PDO::FETCH_ASSOC);

        // Fetch all tier assignments
        $stmtTiers = $db->prepare("
            SELECT character_id, tier_rank
            FROM tier_assignments
            ORDER BY character_id
        ");
        $stmtTiers->execute();
        $tiers = $stmtTiers->fetchAll(PDO::FETCH_ASSOC);

        // Build assignments map
        $assignments = [];
        foreach ($tiers as $tier) {
            $assignments[$tier['character_id']] = $tier['tier_rank'];
        }

        echo json_encode([
            'success' => true,
            'characters' => $characters,
            'assignments' => $assignments
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching tier list: ' . $e->getMessage()
        ]);
    }
}

function assignTier() {
    global $db;
    
    $characterId = isset($_POST['character_id']) ? intval($_POST['character_id']) : null;
    $tier = isset($_POST['tier']) ? $_POST['tier'] : null;

    if (!$characterId || !$tier) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Missing character_id or tier'
        ]);
        return;
    }

    // Validate tier
    if (!in_array($tier, ['S', 'A', 'B', 'C', 'D', 'E', 'F'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid tier'
        ]);
        return;
    }

    try {
        // Check if character exists
        $stmtCheck = $db->prepare("SELECT character_id FROM characters WHERE character_id = ?");
        $stmtCheck->execute([$characterId]);
        if (!$stmtCheck->fetch()) {
            throw new Exception('Character not found');
        }

        // Insert or update tier assignment
        $stmt = $db->prepare("
            INSERT INTO tier_assignments (character_id, tier_rank, assignment_date)
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE tier_rank = ?, assignment_date = NOW()
        ");
        $stmt->execute([$characterId, $tier, $tier]);

        echo json_encode([
            'success' => true,
            'message' => 'Character assigned to tier ' . $tier
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error assigning tier: ' . $e->getMessage()
        ]);
    }
}

function removeTier() {
    global $db;
    
    $characterId = isset($_POST['character_id']) ? intval($_POST['character_id']) : null;

    if (!$characterId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Missing character_id'
        ]);
        return;
    }

    try {
        $stmt = $db->prepare("DELETE FROM tier_assignments WHERE character_id = ?");
        $stmt->execute([$characterId]);

        echo json_encode([
            'success' => true,
            'message' => 'Character removed from tier list'
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error removing tier: ' . $e->getMessage()
        ]);
    }
}

function resetAll() {
    global $db;
    
    try {
        $stmt = $db->prepare("TRUNCATE TABLE tier_assignments");
        $stmt->execute();

        echo json_encode([
            'success' => true,
            'message' => 'All tier assignments cleared'
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error resetting tier list: ' . $e->getMessage()
        ]);
    }
}

?>
