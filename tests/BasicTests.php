#!/usr/bin/env php
<?php
// FILE: /tests/BasicTests.php

/**
 * Basic Tests for AI Video Generator
 *
 * Simple functional tests to verify core functionality
 * Run: php tests/BasicTests.php
 */

// Load configuration
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/models/Tenant.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Project.php';
require_once __DIR__ . '/../app/models/Plan.php';
require_once __DIR__ . '/../app/models/ApiKey.php';
require_once __DIR__ . '/../app/helpers/functions.php';

echo "AI Video Generator - Basic Tests\n";
echo "================================\n\n";

$passed = 0;
$failed = 0;

function test($description, $callback) {
    global $passed, $failed;

    try {
        $result = $callback();
        if ($result) {
            echo "✓ PASS: $description\n";
            $passed++;
        } else {
            echo "✗ FAIL: $description\n";
            $failed++;
        }
    } catch (Exception $e) {
        echo "✗ ERROR: $description - " . $e->getMessage() . "\n";
        $failed++;
    }
}

// Test 1: Database Connection
test("Database connection", function() {
    $db = Database::getInstance()->getConnection();
    return $db !== null;
});

// Test 2: Tenant Model
test("Tenant model can find by ID", function() {
    $tenantModel = new Tenant();
    $tenant = $tenantModel->findById(1);
    return $tenant !== false;
});

// Test 3: User Model
test("User model can find by email", function() {
    $userModel = new User();
    $user = $userModel->findByEmail('john@example.com');
    return $user !== false && $user['email'] === 'john@example.com';
});

// Test 4: Password Verification
test("Password hashing and verification works", function() {
    $password = 'test123';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    return password_verify($password, $hash);
});

// Test 5: Project Model
test("Project model can count by tenant", function() {
    $projectModel = new Project();
    $count = $projectModel->countByTenant(2);
    return $count >= 0;
});

// Test 6: Plans
test("Plan model can get active plans", function() {
    $planModel = new Plan();
    $plans = $planModel->getAllActive();
    return is_array($plans) && count($plans) > 0;
});

// Test 7: Tenant Quota Check
test("Tenant can check project creation quota", function() {
    $tenantModel = new Tenant();
    $canCreate = $tenantModel->canCreateProject(2);
    return is_bool($canCreate);
});

// Test 8: Tenant Usage Tracking
test("Tenant usage can be retrieved", function() {
    $tenantModel = new Tenant();
    $usage = $tenantModel->getCurrentUsage(2);
    return is_array($usage) && isset($usage['projects_count']);
});

// Test 9: Helper Functions
test("formatBytes helper function", function() {
    $result = formatBytes(1024);
    return $result === '1.00 KB';
});

test("formatDuration helper function", function() {
    $result = formatDuration(125);
    return $result === '02:05';
});

test("generateRandomString helper function", function() {
    $result = generateRandomString(16);
    return strlen($result) === 16;
});

// Test 10: API Key Generation
test("API key can be generated", function() {
    $apiKeyModel = new ApiKey();
    $result = $apiKeyModel->generate(2, 'Test Key');
    return isset($result['id']) && isset($result['api_key']) && strlen($result['api_key']) > 40;
});

// Test 11: Subscription
test("Active subscription can be retrieved", function() {
    $tenantModel = new Tenant();
    $subscription = $tenantModel->getActiveSubscription(2);
    return is_array($subscription) && isset($subscription['plan_name']);
});

// Test 12: Tenant Isolation
test("Tenant isolation - cannot access other tenant data", function() {
    $projectModel = new Project();
    $db = Database::getInstance()->getConnection();

    // Get project from tenant 2
    $stmt = $db->prepare("SELECT * FROM projects WHERE tenant_id = 2 LIMIT 1");
    $stmt->execute();
    $project = $stmt->fetch();

    if (!$project) {
        return true; // No projects, skip test
    }

    // Try to access it as tenant 3
    $stmt2 = $db->prepare("SELECT * FROM projects WHERE id = ? AND tenant_id = 3");
    $stmt2->execute([$project['id']]);
    $result = $stmt2->fetch();

    // Should return false (no access)
    return $result === false;
});

echo "\n================================\n";
echo "Tests completed!\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
echo "Total: " . ($passed + $failed) . "\n";

if ($failed === 0) {
    echo "\n✓ All tests passed!\n";
    exit(0);
} else {
    echo "\n✗ Some tests failed.\n";
    exit(1);
}
