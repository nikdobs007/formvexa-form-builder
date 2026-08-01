<?php

namespace formvexa\Core;

defined('ABSPATH') || exit;

/**
 * View renderer
 */
final class View
{

    /**
     * Render view file.
     *
     * @param string $view View path.
     * @param array  $data Data.
     *
     * @return void
     */
    public static function render(string $view, array $data = []): void
    {

        $file = NDFB_PLUGIN_PATH . 'app/Views/' . $view . '.php';

        if (file_exists($file)) {
            extract($data, EXTR_SKIP);
            include $file;
            return;
        }

        echo '<div class="error">View not found: ' . esc_html($view) . '</div>';
    }
}