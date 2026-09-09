<?php
declare(strict_types=1);

namespace App\Core;

final class View
{
    // Parameters (and every other local here) are prefixed with __ so none
    // of them can ever collide with a key in $data — e.g. a controller
    // passing ['template' => $row]. A plain $template/$layout local would
    // already occupy that name before extract() runs, so EXTR_SKIP would
    // silently keep the wrong value and the view would see a string where
    // it expected the controller's data.
    public static function render(string $__template, array $data = [], ?string $__layout = 'layouts/app'): void
    {
        $__viewFile = BASE_PATH . '/views/' . $__template . '.php';
        if (!file_exists($__viewFile)) {
            throw new \RuntimeException("View not found: $__template");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $__viewFile;
        $__viewContent = ob_get_clean();

        if ($__layout === null) {
            echo $__viewContent;
            return;
        }

        $content = $__viewContent;
        $__layoutFile = BASE_PATH . '/views/' . $__layout . '.php';
        require $__layoutFile;
    }

    public static function partial(string $__template, array $data = []): void
    {
        $__viewFile = BASE_PATH . '/views/' . $__template . '.php';
        extract($data, EXTR_SKIP);
        require $__viewFile;
    }
}
