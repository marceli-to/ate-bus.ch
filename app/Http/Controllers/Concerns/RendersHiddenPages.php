<?php

namespace App\Http\Controllers\Concerns;

use Statamic\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pages that are only reachable by link (Fundmeldung, Unfallmeldung): never indexed,
 * never cached, and the code in the link is not passed on via the referrer.
 */
trait RendersHiddenPages
{
    /**
     * Render an Antlers template in the thin layout, with the Vue config as JSON.
     *
     * @param  array<string, mixed>  $config  for the Vue form, as {{ form_config }}
     * @param  array<string, mixed>  $variables  for the template
     */
    protected function hiddenPage(string $template, string $title, string $canonical, array $config = [], int $status = 200, array $variables = []): Response
    {
        $html = View::make($template)
            ->layout('templates/layout-thin')
            ->with([
                'title' => $title,
                'robots' => 'noindex_nofollow',
                'canonical_url' => $canonical,
                // Rendered into <script type="application/json">: mask </script> and friends
                'form_config' => json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE),
                ...$variables,
            ])
            ->render();

        return $this->hidden(response($html, $status));
    }

    protected function hidden(Response $response): Response
    {
        return $response->withHeaders([
            'X-Robots-Tag' => 'noindex, nofollow, noarchive',
            'Referrer-Policy' => 'no-referrer',
            'Cache-Control' => 'no-store',
        ]);
    }
}
