<?php

namespace App\Core;

class Renderer
{
    private string $viewsPath;
    private string $defaultLayout = 'layouts/default';

    public function __construct(string $viewsPath)
    {
        $this->viewsPath = rtrim($viewsPath, '/\\');
    }

    public function render(string $view, array $data = [], string|bool|null $layout = null): void
    {
        $viewFile = $this->viewsPath . '/' . $view . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("Vue introuvable : {$viewFile}");
        }

        // Rend les variables du tableau disponibles dans la vue
        extract($data);

        // Capture le rendu de la vue
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Détermine quel layout utiliser
        $layoutToUse = $layout ?? $this->defaultLayout;

        if ($layoutToUse === false) {
            echo $content;
            return;
        }

        $layoutFile = $this->viewsPath . '/' . $layoutToUse . '.php';

        if (!file_exists($layoutFile)) {
            throw new \RuntimeException("Layout introuvable : {$layoutFile}");
        }

        require $layoutFile;
    }
}
