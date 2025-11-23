// FILE: /app/controllers/AssetController.php
<?php

/**
 * AssetController
 *
 * Handles media assets (images, videos, audio)
 */
class AssetController extends Controller
{
    /**
     * List all assets
     */
    public function index()
    {
        $this->requireAuth();

        $tenantId = $this->getTenantId();
        $user = $this->getAuthUser();

        $assetModel = $this->model('Asset');

        // Get filters
        $filters = [
            'file_type' => $_GET['file_type'] ?? '',
            'search' => $_GET['search'] ?? '',
        ];

        // Pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;

        $assets = $assetModel->findByTenantWithPagination($tenantId, $page, $perPage, $filters);
        $totalAssets = $assetModel->countByTenant($tenantId, $filters);
        $totalPages = totalPages($totalAssets, $perPage);

        // Get storage usage
        $storageUsed = $assetModel->getTotalStorageByTenant($tenantId);

        $this->view->render('assets/index', [
            'user' => $user,
            'assets' => $assets,
            'filters' => $filters,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalAssets' => $totalAssets,
            'storageUsed' => $storageUsed,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Show upload form
     */
    public function upload()
    {
        $this->requireAuth();

        $user = $this->getAuthUser();

        $this->view->render('assets/upload', [
            'user' => $user,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Handle file upload
     */
    public function store()
    {
        $this->requireAuth();
        $this->validateCsrfToken();

        $tenantId = $this->getTenantId();
        $userId = $_SESSION['user_id'];

        // Check if file was uploaded
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            setFlash('error', 'Please select a file to upload');
            $this->redirect('/assets/upload');
        }

        $file = $_FILES['file'];
        $config = require __DIR__ . '/../../config/app.php';

        // Get file info
        $originalFilename = $file['name'];
        $fileSize = $file['size'];
        $tmpPath = $file['tmp_name'];
        $mimeType = mime_content_type($tmpPath);

        // Determine file type
        $extension = getFileExtension($originalFilename);

        if (in_array($extension, $config['upload']['allowed_image_types'])) {
            $fileType = 'image';
            $allowedTypes = $config['upload']['allowed_image_types'];
        } elseif (in_array($extension, $config['upload']['allowed_video_types'])) {
            $fileType = 'video';
            $allowedTypes = $config['upload']['allowed_video_types'];
        } elseif (in_array($extension, $config['upload']['allowed_audio_types'])) {
            $fileType = 'audio';
            $allowedTypes = $config['upload']['allowed_audio_types'];
        } else {
            setFlash('error', 'File type not allowed');
            $this->redirect('/assets/upload');
        }

        // Validate file type
        if (!validateFileType($originalFilename, $allowedTypes)) {
            setFlash('error', 'Invalid file type');
            $this->redirect('/assets/upload');
        }

        // Validate file size
        if ($fileSize > $config['upload']['max_size']) {
            setFlash('error', 'File size exceeds maximum allowed size');
            $this->redirect('/assets/upload');
        }

        // Generate unique filename
        $newFilename = generateUniqueFilename($originalFilename);

        // Create upload directory if not exists
        $uploadDir = __DIR__ . '/../../storage/uploads/' . $fileType . 's/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filePath = $uploadDir . $newFilename;
        $relativeFilePath = '/storage/uploads/' . $fileType . 's/' . $newFilename;

        // Move uploaded file
        if (!move_uploaded_file($tmpPath, $filePath)) {
            setFlash('error', 'Failed to upload file');
            $this->redirect('/assets/upload');
        }

        // Get additional metadata
        $width = null;
        $height = null;
        $duration = null;

        if ($fileType === 'image') {
            $imageInfo = getimagesize($filePath);
            if ($imageInfo) {
                $width = $imageInfo[0];
                $height = $imageInfo[1];
            }
        }

        // Get asset name and tags from form
        $name = trim($_POST['name'] ?? pathinfo($originalFilename, PATHINFO_FILENAME));
        $tags = trim($_POST['tags'] ?? '');

        // Save to database
        $assetModel = $this->model('Asset');

        $assetId = $assetModel->insert([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'name' => $name,
            'filename' => $newFilename,
            'file_path' => $relativeFilePath,
            'file_type' => $fileType,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'width' => $width,
            'height' => $height,
            'duration' => $duration,
            'tags' => $tags,
        ]);

        // Update storage usage
        $usageModel = $this->model('UsageTracking');
        $totalStorage = $assetModel->getTotalStorageByTenant($tenantId);
        $usageModel->updateStorage($tenantId, ceil($totalStorage / (1024 * 1024)));

        // Track analytics
        $analyticsModel = $this->model('Analytics');
        $analyticsModel->track($tenantId, $userId, 'asset_uploaded', [
            'asset_id' => $assetId,
            'file_type' => $fileType,
            'file_size' => $fileSize,
        ]);

        setFlash('success', 'Asset uploaded successfully!');
        $this->redirect('/assets');
    }

    /**
     * Show asset details
     */
    public function show($id)
    {
        $this->requireAuth();

        $tenantId = $this->getTenantId();
        $user = $this->getAuthUser();

        $assetModel = $this->model('Asset');

        $asset = $this->db->prepare("SELECT * FROM assets WHERE id = ? AND tenant_id = ?");
        $asset->execute([$id, $tenantId]);
        $asset = $asset->fetch();

        if (!$asset) {
            setFlash('error', 'Asset not found');
            $this->redirect('/assets');
        }

        $this->view->render('assets/show', [
            'user' => $user,
            'asset' => $asset,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Delete asset
     */
    public function delete($id)
    {
        $this->requireAuth();
        $this->validateCsrfToken();

        $tenantId = $this->getTenantId();

        $assetModel = $this->model('Asset');

        // Verify asset belongs to tenant
        $asset = $this->db->prepare("SELECT * FROM assets WHERE id = ? AND tenant_id = ?");
        $asset->execute([$id, $tenantId]);
        $asset = $asset->fetch();

        if (!$asset) {
            setFlash('error', 'Asset not found');
            $this->redirect('/assets');
        }

        // Delete file from filesystem
        $filePath = __DIR__ . '/../../' . $asset['file_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Delete from database
        $assetModel->delete($id);

        // Update storage usage
        $usageModel = $this->model('UsageTracking');
        $totalStorage = $assetModel->getTotalStorageByTenant($tenantId);
        $usageModel->updateStorage($tenantId, ceil($totalStorage / (1024 * 1024)));

        setFlash('success', 'Asset deleted successfully');
        $this->redirect('/assets');
    }
}
