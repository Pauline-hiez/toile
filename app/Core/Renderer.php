<?php

namespace App\Core;

class Renderer
{
    private string $viewsPath;

    public function __construct(string $viewsPath)
    {
        $this->viewsPath = rtrim($viewsPath, '/\\');
    }

    public function render(string $view, array $data = []): void
    {
        $file = $this->viewsPath . '/' . $view . '.php';

        if (!is_file($file)) {
            throw new \RuntimeException("Vue introuvable : {$file}");
        }

        extract($data);

        require $file;
    }
}
