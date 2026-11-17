<?php

use Valet\Drivers\LaravelValetDriver;

/**
 * Herd/Valet (nur lokal): eigenständige PHP-Dateien in public/ direkt ausführen,
 * statt sie wie alles andere über public/index.php zu leiten.
 * Auf dem Server (nginx/Apache) wird diese Datei nicht verwendet.
 */
class LocalValetDriver extends LaravelValetDriver
{
    private const STANDALONE = ['/fundmeldung.php'];

    public function frontControllerPath(string $sitePath, string $siteName, string $uri): ?string
    {
        if (in_array($uri, self::STANDALONE, true) && file_exists($file = $sitePath . '/public' . $uri)) {
            $_SERVER['SCRIPT_FILENAME'] = $file;
            $_SERVER['SCRIPT_NAME'] = $uri;
            $_SERVER['DOCUMENT_ROOT'] = $sitePath . '/public';

            return $file;
        }

        return parent::frontControllerPath($sitePath, $siteName, $uri);
    }
}
