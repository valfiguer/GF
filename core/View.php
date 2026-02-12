<?php
/**
 * Template renderer.
 * render() wraps a page template inside base.php.
 * partial() includes a sub-template with its own scope.
 */
class View {
    private static string $viewsDir = '';

    public static function init(string $dir): void {
        self::$viewsDir = rtrim($dir, '/');
    }

    /**
     * Render a page template wrapped in base.php.
     * The page template is captured and injected as $content.
     */
    public static function render(string $template, array $data = []): void {
        // Capture inner template
        $data['content'] = self::capture($template, $data);
        // Render base layout
        extract($data, EXTR_SKIP);
        require self::$viewsDir . '/base.php';
    }

    /** Render a template and return the output as a string. */
    public static function capture(string $template, array $data = []): string {
        ob_start();
        extract($data, EXTR_SKIP);
        require self::$viewsDir . '/' . $template . '.php';
        return ob_get_clean();
    }

    /** Include a partial with its own variable scope. */
    public static function partial(string $name, array $data = []): void {
        extract($data, EXTR_SKIP);
        require self::$viewsDir . '/partials/' . $name . '.php';
    }

    /** Send a JSON response. */
    public static function renderJson($data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
