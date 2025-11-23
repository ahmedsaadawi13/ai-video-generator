#!/usr/bin/env php
<?php
// FILE: /app/console/process-renders.php

/**
 * Process Render Jobs CLI Script
 *
 * This script processes queued render jobs in the background.
 * Run: php app/console/process-renders.php
 *
 * In production, this would be run as a cron job or background worker.
 */

// Load core classes
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../models/RenderJob.php';
require_once __DIR__ . '/../models/Video.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../models/UsageTracking.php';
require_once __DIR__ . '/../services/AiVideoService.php';
require_once __DIR__ . '/../helpers/functions.php';

echo "AI Video Generator - Render Job Processor\n";
echo "==========================================\n\n";

// Set timezone
date_default_timezone_set('UTC');

$renderJobModel = new RenderJob();
$videoModel = new Video();
$notificationModel = new Notification();
$usageModel = new UsageTracking();
$aiService = new AiVideoService();

// Get queued jobs
$queuedJobs = $renderJobModel->getQueued(10);

if (empty($queuedJobs)) {
    echo "No queued render jobs found.\n";
    exit(0);
}

echo "Found " . count($queuedJobs) . " queued render job(s).\n\n";

foreach ($queuedJobs as $job) {
    echo "Processing Job #{$job['id']} ({$job['job_type']})...\n";

    // Update to processing
    $renderJobModel->updateStatus($job['id'], 'processing', 10);

    // Simulate AI processing stages
    $parameters = json_decode($job['parameters'], true);
    $duration = isset($parameters['duration']) ? (int)$parameters['duration'] : 30;

    // Simulate processing progress
    $stages = [25, 50, 75, 90];

    foreach ($stages as $progress) {
        $renderJobModel->updateStatus($job['id'], 'processing', $progress);
        echo "  Progress: {$progress}%\n";
        sleep(1); // Simulate processing time
    }

    // Generate output file (simulated)
    $output = $aiService->generateOutputFile($job['id'], $parameters);

    // Create video record
    $videoId = $videoModel->insert([
        'tenant_id' => $job['tenant_id'],
        'project_id' => $job['project_id'],
        'render_job_id' => $job['id'],
        'user_id' => $job['user_id'],
        'title' => 'Rendered Video - Job #' . $job['id'],
        'description' => 'Auto-generated from ' . $job['job_type'],
        'filename' => $output['filename'],
        'file_path' => $output['file_path'],
        'file_size' => $output['file_size'],
        'duration' => $output['duration'],
        'resolution' => $parameters['resolution'] ?? '1080p',
        'thumbnail_url' => $output['thumbnail_url'],
        'public_token' => $videoModel->generatePublicToken(),
    ]);

    // Update job to completed
    $renderJobModel->updateStatus($job['id'], 'completed', 100);

    // Update usage stats
    $usageModel->incrementRenders($job['tenant_id'], ceil($duration / 60));

    // Create notification
    $notificationModel->create(
        $job['tenant_id'],
        $job['user_id'],
        'render_completed',
        'Video Render Completed',
        'Your video has been successfully rendered and is ready to download.'
    );

    echo "  ✓ Job #{$job['id']} completed successfully!\n";
    echo "  ✓ Video #{$videoId} created: {$output['filename']}\n\n";
}

echo "All jobs processed.\n";
