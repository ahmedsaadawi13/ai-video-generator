// FILE: /app/controllers/SceneController.php
<?php

/**
 * SceneController
 *
 * Handles scenes within projects
 */
class SceneController extends Controller
{
    /**
     * List scenes for a project
     */
    public function index($projectId)
    {
        $this->requireAuth();

        $tenantId = $this->getTenantId();
        $user = $this->getAuthUser();

        $projectModel = $this->model('Project');
        $sceneModel = $this->model('Scene');

        $project = $this->db->prepare("SELECT * FROM projects WHERE id = ? AND tenant_id = ?");
        $project->execute([$projectId, $tenantId]);
        $project = $project->fetch();

        if (!$project) {
            setFlash('error', 'Project not found');
            $this->redirect('/projects');
        }

        $scenes = $sceneModel->findByProject($projectId, $tenantId);
        $totalDuration = $sceneModel->getTotalDuration($projectId);

        $this->view->render('scenes/index', [
            'user' => $user,
            'project' => $project,
            'scenes' => $scenes,
            'totalDuration' => $totalDuration,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Show create scene form
     */
    public function create($projectId)
    {
        $this->requireAuth();

        $tenantId = $this->getTenantId();
        $user = $this->getAuthUser();

        $projectModel = $this->model('Project');
        $assetModel = $this->model('Asset');
        $stylePresetModel = $this->model('StylePreset');

        $project = $this->db->prepare("SELECT * FROM projects WHERE id = ? AND tenant_id = ?");
        $project->execute([$projectId, $tenantId]);
        $project = $project->fetch();

        if (!$project) {
            setFlash('error', 'Project not found');
            $this->redirect('/projects');
        }

        $assets = $assetModel->findByTenant($tenantId);
        $stylePresets = $stylePresetModel->getAllActive();

        $this->view->render('scenes/create', [
            'user' => $user,
            'project' => $project,
            'assets' => $assets,
            'stylePresets' => $stylePresets,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Store new scene
     */
    public function store($projectId)
    {
        $this->requireAuth();
        $this->validateCsrfToken();

        $tenantId = $this->getTenantId();

        // Verify project
        $project = $this->db->prepare("SELECT * FROM projects WHERE id = ? AND tenant_id = ?");
        $project->execute([$projectId, $tenantId]);
        $project = $project->fetch();

        if (!$project) {
            setFlash('error', 'Project not found');
            $this->redirect('/projects');
        }

        $sceneModel = $this->model('Scene');

        $sceneType = $_POST['scene_type'] ?? 'text_only';
        $duration = (int)($_POST['duration'] ?? 5);
        $prompt = trim($_POST['prompt'] ?? '');
        $textContent = trim($_POST['text_content'] ?? '');
        $assetId = !empty($_POST['asset_id']) ? (int)$_POST['asset_id'] : null;
        $stylePresetId = !empty($_POST['style_preset_id']) ? (int)$_POST['style_preset_id'] : null;

        $position = $sceneModel->getNextPosition($projectId);

        $sceneModel->insert([
            'tenant_id' => $tenantId,
            'project_id' => $projectId,
            'position' => $position,
            'scene_type' => $sceneType,
            'duration' => $duration,
            'prompt' => $prompt,
            'text_content' => $textContent,
            'asset_id' => $assetId,
            'style_preset_id' => $stylePresetId,
        ]);

        setFlash('success', 'Scene added successfully!');
        $this->redirect('/projects/' . $projectId . '/scenes');
    }

    /**
     * Show edit scene form
     */
    public function edit($id)
    {
        $this->requireAuth();

        $tenantId = $this->getTenantId();
        $user = $this->getAuthUser();

        $sceneModel = $this->model('Scene');
        $assetModel = $this->model('Asset');
        $stylePresetModel = $this->model('StylePreset');

        $scene = $this->db->prepare("SELECT s.*, p.name as project_name FROM scenes s JOIN projects p ON s.project_id = p.id WHERE s.id = ? AND s.tenant_id = ?");
        $scene->execute([$id, $tenantId]);
        $scene = $scene->fetch();

        if (!$scene) {
            setFlash('error', 'Scene not found');
            $this->redirect('/projects');
        }

        $assets = $assetModel->findByTenant($tenantId);
        $stylePresets = $stylePresetModel->getAllActive();

        $this->view->render('scenes/edit', [
            'user' => $user,
            'scene' => $scene,
            'assets' => $assets,
            'stylePresets' => $stylePresets,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Update scene
     */
    public function update($id)
    {
        $this->requireAuth();
        $this->validateCsrfToken();

        $tenantId = $this->getTenantId();

        $sceneModel = $this->model('Scene');

        // Verify scene belongs to tenant
        $scene = $this->db->prepare("SELECT * FROM scenes WHERE id = ? AND tenant_id = ?");
        $scene->execute([$id, $tenantId]);
        $scene = $scene->fetch();

        if (!$scene) {
            setFlash('error', 'Scene not found');
            $this->redirect('/projects');
        }

        $sceneType = $_POST['scene_type'] ?? $scene['scene_type'];
        $duration = (int)($_POST['duration'] ?? $scene['duration']);
        $prompt = trim($_POST['prompt'] ?? '');
        $textContent = trim($_POST['text_content'] ?? '');
        $assetId = !empty($_POST['asset_id']) ? (int)$_POST['asset_id'] : null;
        $stylePresetId = !empty($_POST['style_preset_id']) ? (int)$_POST['style_preset_id'] : null;

        $sceneModel->update($id, [
            'scene_type' => $sceneType,
            'duration' => $duration,
            'prompt' => $prompt,
            'text_content' => $textContent,
            'asset_id' => $assetId,
            'style_preset_id' => $stylePresetId,
        ]);

        setFlash('success', 'Scene updated successfully!');
        $this->redirect('/projects/' . $scene['project_id'] . '/scenes');
    }

    /**
     * Delete scene
     */
    public function delete($id)
    {
        $this->requireAuth();
        $this->validateCsrfToken();

        $tenantId = $this->getTenantId();

        $sceneModel = $this->model('Scene');

        // Verify scene belongs to tenant
        $scene = $this->db->prepare("SELECT * FROM scenes WHERE id = ? AND tenant_id = ?");
        $scene->execute([$id, $tenantId]);
        $scene = $scene->fetch();

        if (!$scene) {
            setFlash('error', 'Scene not found');
            $this->redirect('/projects');
        }

        $projectId = $scene['project_id'];

        $sceneModel->delete($id);

        setFlash('success', 'Scene deleted successfully');
        $this->redirect('/projects/' . $projectId . '/scenes');
    }
}
