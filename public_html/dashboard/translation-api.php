<?php

/**
 * Translation API - Handles saving/loading translations for all entity types
 * Called via AJAX from the dashboard language tabs
 */

require_once __DIR__ . '/_guard.php';
dash_require('pages');

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . "/../../core/language.php";
require_once __DIR__ . "/../../core/translations.php";

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$entityType = $_GET['entity_type'] ?? $_POST['entity_type'] ?? '';
$entityId = intval($_GET['entity_id'] ?? $_POST['entity_id'] ?? 0);
$locale = $_GET['locale'] ?? $_POST['locale'] ?? '';

try {
    if (!$entityType || !$entityId) {
        throw new Exception("Missing entity_type or entity_id");
    }

    if ($action === 'get') {
        // Get all translations for an entity
        $translations = getAllEntityTranslations($entityType, $entityId);
        
        // Also fetch base entity data
        $tableMap = [
            'page' => 'pages',
            'campaign' => 'campaigns',
            'news' => 'news',
            'hero_slide' => 'hero_slides',
            'course' => 'courses',
            'employee' => 'employee_profiles',
        ];
        
        $baseTable = $tableMap[$entityType] ?? null;
        $baseData = null;
        
        if ($baseTable) {
            $stmt = $pdo->prepare("SELECT * FROM `$baseTable` WHERE id = ?");
            $stmt->execute([$entityId]);
            $baseData = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        echo json_encode([
            "status" => "success",
            "base" => $baseData,
            "translations" => $translations,
            "locales" => SUPPORTED_LOCALES,
        ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        
    } elseif ($action === 'save') {
        // Save translation for an entity
        $raw = file_get_contents("php://input");
        $data = json_decode($raw, true);
        
        if (!$data) {
            throw new Exception("No JSON data received");
        }
        
        $locale = $data['locale'] ?? '';
        $fields = $data['fields'] ?? [];
        
        if (!in_array($locale, SUPPORTED_LOCALES, true)) {
            throw new Exception("Invalid locale: $locale");
        }
        
        if (empty($fields)) {
            throw new Exception("No fields to save");
        }
        
        $success = saveEntityTranslation($entityType, $entityId, $locale, $fields);
        
        if ($success) {
            echo json_encode([
                "status" => "success",
                "message" => "Translation saved for $locale",
            ], JSON_UNESCAPED_UNICODE);
        } else {
            throw new Exception("Failed to save translation");
        }
        
    } elseif ($action === 'delete') {
        // Delete translation
        if (!$locale) {
            throw new Exception("Missing locale");
        }
        
        $success = deleteEntityTranslation($entityType, $entityId, $locale);
        
        echo json_encode([
            "status" => "success",
            "message" => "Translation deleted for $locale",
        ], JSON_UNESCAPED_UNICODE);
        
    } else {
        throw new Exception("Invalid action: $action");
    }
    
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}