// FILE: /app/core/View.php
<?php

/**
 * View Class
 *
 * Handles rendering of views with layout support
 */
class View
{
    private $layout = 'default';
    private $data = [];

    /**
     * Set layout template
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    /**
     * Render a view
     */
    public function render($view, $data = [])
    {
        $this->data = $data;

        // Extract data to variables
        extract($data);

        // Start output buffering
        ob_start();

        $viewFile = __DIR__ . '/../views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            die("View not found: $view");
        }

        // Include the view file
        include $viewFile;

        // Get view content
        $content = ob_get_clean();

        // If layout is set, render with layout
        if ($this->layout) {
            $layoutFile = __DIR__ . '/../views/layouts/' . $this->layout . '.php';

            if (file_exists($layoutFile)) {
                include $layoutFile;
            } else {
                echo $content;
            }
        } else {
            echo $content;
        }
    }

    /**
     * Render partial view
     */
    public function partial($view, $data = [])
    {
        extract($data);

        $viewFile = __DIR__ . '/../views/' . $view . '.php';

        if (file_exists($viewFile)) {
            include $viewFile;
        }
    }

    /**
     * Escape HTML output
     */
    public function escape($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Generate URL
     */
    public function url($path)
    {
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        return $basePath . '/' . ltrim($path, '/');
    }

    /**
     * Get CSRF token
     */
    public function csrfToken()
    {
        return $_SESSION['csrf_token'] ?? '';
    }

    /**
     * Get flash message
     */
    public function flash($key)
    {
        if (isset($_SESSION['flash'][$key])) {
            $message = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $message;
        }
        return null;
    }
}
