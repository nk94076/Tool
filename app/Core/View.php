<?php
declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $template, array $data = [], ?string $layout = 'layouts/app'): void
    {
        extract($data, EXTR_SKIP);
        ob_start();
        $file = BASE_PATH . '/views/' . $template . '.php';
        if (!file_exists($file)) {
            throw new \RuntimeException("View not found: $template");
        }
        require $file;
        $content = ob_get_clean();

        if ($layout === null) {
            echo $content;
            return;
        }

        $layoutFile = BASE_PATH . '/views/' . $layout . '.php';
        require $layoutFile;
    }

    public static function partial(string $template, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require BASE_PATH . '/views/' . $template . '.php';
    }
}
