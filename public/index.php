// FILE: /public/index.php
<?php

/**
 * Application Entry Point
 *
 * This is the main entry point for the AI Video Generator SaaS application
 */

// Start session
session_start();

// Set timezone to UTC
date_default_timezone_set('UTC');

// Load configuration
$config = require __DIR__ . '/../config/app.php';

// Error reporting based on environment
if ($config['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Load core classes
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/core/View.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/core/Router.php';

// Load helpers
require_once __DIR__ . '/../app/helpers/functions.php';

// Initialize router
$router = new Router();

// ========================================
// PUBLIC ROUTES
// ========================================

$router->get('/', 'HomeController', 'index');
$router->get('/login', 'AuthController', 'login');
$router->post('/login', 'AuthController', 'loginPost');
$router->get('/register', 'AuthController', 'register');
$router->post('/register', 'AuthController', 'registerPost');
$router->get('/logout', 'AuthController', 'logout');

// ========================================
// DASHBOARD
// ========================================

$router->get('/dashboard', 'DashboardController', 'index');

// ========================================
// PROJECTS
// ========================================

$router->get('/projects', 'ProjectController', 'index');
$router->get('/projects/create', 'ProjectController', 'create');
$router->post('/projects/create', 'ProjectController', 'store');
$router->get('/projects/:id', 'ProjectController', 'show');
$router->get('/projects/:id/edit', 'ProjectController', 'edit');
$router->post('/projects/:id/edit', 'ProjectController', 'update');
$router->post('/projects/:id/delete', 'ProjectController', 'delete');

// ========================================
// SCENES
// ========================================

$router->get('/projects/:id/scenes', 'SceneController', 'index');
$router->get('/projects/:id/scenes/create', 'SceneController', 'create');
$router->post('/projects/:id/scenes/create', 'SceneController', 'store');
$router->get('/scenes/:id/edit', 'SceneController', 'edit');
$router->post('/scenes/:id/edit', 'SceneController', 'update');
$router->post('/scenes/:id/delete', 'SceneController', 'delete');

// ========================================
// ASSETS
// ========================================

$router->get('/assets', 'AssetController', 'index');
$router->get('/assets/upload', 'AssetController', 'upload');
$router->post('/assets/upload', 'AssetController', 'store');
$router->get('/assets/:id', 'AssetController', 'show');
$router->post('/assets/:id/delete', 'AssetController', 'delete');

// ========================================
// TEMPLATES
// ========================================

$router->get('/templates', 'TemplateController', 'index');
$router->get('/templates/create', 'TemplateController', 'create');
$router->post('/templates/create', 'TemplateController', 'store');
$router->get('/templates/:id', 'TemplateController', 'show');
$router->post('/templates/:id/delete', 'TemplateController', 'delete');

// ========================================
// RENDER JOBS
// ========================================

$router->get('/renders', 'RenderController', 'index');
$router->get('/renders/create', 'RenderController', 'create');
$router->post('/renders/create', 'RenderController', 'store');
$router->get('/renders/:id', 'RenderController', 'show');
$router->post('/renders/:id/retry', 'RenderController', 'retry');

// ========================================
// VIDEOS
// ========================================

$router->get('/videos', 'VideoController', 'index');
$router->get('/videos/:id', 'VideoController', 'show');
$router->get('/videos/:id/download', 'VideoController', 'download');
$router->post('/videos/:id/duplicate', 'VideoController', 'duplicate');

// ========================================
// TENANT MANAGEMENT
// ========================================

$router->get('/tenant/settings', 'TenantController', 'settings');
$router->post('/tenant/settings', 'TenantController', 'updateSettings');
$router->get('/tenant/brand', 'TenantController', 'brand');
$router->post('/tenant/brand', 'TenantController', 'updateBrand');

// ========================================
// USER MANAGEMENT
// ========================================

$router->get('/users', 'UserController', 'index');
$router->get('/users/create', 'UserController', 'create');
$router->post('/users/create', 'UserController', 'store');
$router->get('/users/:id/edit', 'UserController', 'edit');
$router->post('/users/:id/edit', 'UserController', 'update');
$router->post('/users/:id/delete', 'UserController', 'delete');

// ========================================
// SUBSCRIPTION & BILLING
// ========================================

$router->get('/subscription', 'SubscriptionController', 'index');
$router->get('/subscription/plans', 'SubscriptionController', 'plans');
$router->post('/subscription/subscribe', 'SubscriptionController', 'subscribe');
$router->get('/subscription/invoices', 'SubscriptionController', 'invoices');
$router->get('/subscription/usage', 'SubscriptionController', 'usage');

// ========================================
// PLATFORM ADMIN
// ========================================

$router->get('/admin', 'AdminController', 'index');
$router->get('/admin/tenants', 'AdminController', 'tenants');
$router->get('/admin/plans', 'AdminController', 'plans');
$router->get('/admin/plans/create', 'AdminController', 'createPlan');
$router->post('/admin/plans/create', 'AdminController', 'storePlan');

// ========================================
// API KEYS
// ========================================

$router->get('/api-keys', 'ApiKeyController', 'index');
$router->post('/api-keys/generate', 'ApiKeyController', 'generate');
$router->post('/api-keys/:id/revoke', 'ApiKeyController', 'revoke');

// ========================================
// PUBLIC API ENDPOINTS
// ========================================

$router->post('/api/v1/render', 'ApiController', 'createRenderJob');
$router->get('/api/v1/render/:id', 'ApiController', 'getRenderJob');

// ========================================
// ANALYTICS
// ========================================

$router->get('/analytics', 'AnalyticsController', 'index');
$router->get('/analytics/reports', 'AnalyticsController', 'reports');

// Dispatch the request
$router->dispatch();
