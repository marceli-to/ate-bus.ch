<?php

namespace App\Support;

/**
 * The accident report HTML is built in the browser (AccidentReportDocument.vue) and sent with the form.
 * It is untrusted input: only plain text, table and image markup survives, images only as embedded PNG/JPEG.
 */
class ReportHtml
{
    private const ALLOWED_TAGS = ['div', 'span', 'p', 'b', 'strong', 'i', 'em', 'u', 'br', 'hr', 'small', 'sup', 'sub',
        'h1', 'h2', 'h3', 'h4', 'h5', 'table', 'thead', 'tbody', 'tr', 'td', 'th', 'img', 'ul', 'ol', 'li', 'a'];

    // Removed together with their content
    private const DROPPED_TAGS = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'textarea',
        'select', 'link', 'meta', 'base', 'svg', 'math', 'video', 'audio', 'canvas', 'template', 'noscript', 'title', 'head'];

    private const ALLOWED_ATTRIBUTES = ['style', 'width', 'height', 'colspan', 'rowspan', 'align', 'alt', 'src', 'href'];

    private const DATA_IMAGE = '#^data:image/(png|jpeg);base64,[A-Za-z0-9+/=\s]+$#';

    public static function sanitize(string $html): string
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><div id="__root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $doc->getElementById('__root') ?? $doc->documentElement;
        self::clean($root);

        $out = '';
        foreach (iterator_to_array($root->childNodes) as $node) {
            $out .= $doc->saveHTML($node);
        }

        return $out;
    }

    /** Plain text version for the control panel */
    public static function toText(string $html): string
    {
        $text = preg_replace('#<(br|/tr|/h4|/div|/p)\s*/?>#i', "\n", $html);
        $text = preg_replace('#</td>\s*<td[^>]*>#i', ': ', $text);
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/[ \t]+/", ' ', $text);

        return trim(preg_replace("/\n\s*\n+/", "\n\n", $text));
    }

    /**
     * Turn embedded images (data:) into inline attachments (cid:), so Outlook shows them too.
     *
     * @param  \Illuminate\Mail\Message|null  $message
     */
    public static function embedImages(string $html, $message): string
    {
        if (! $message) {
            return $html;
        }

        $i = 0;

        return preg_replace_callback('#src="data:image/(png|jpeg);base64,([A-Za-z0-9+/=\s]+)"#', function ($match) use ($message, &$i) {
            $i++;
            $extension = $match[1] === 'png' ? 'png' : 'jpg';

            return 'src="' . $message->embedData(base64_decode($match[2]), "bild$i.$extension", "image/{$match[1]}") . '"';
        }, $html);
    }

    /** Standalone HTML document (attachment «Unfallbericht.html» and stored copy) */
    public static function document(string $title, string $report): string
    {
        $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

        return '<!DOCTYPE html><html lang="de"><head><meta charset="utf-8"><title>' . $title . '</title>'
            . '<style>body{font-family:Arial,Helvetica,sans-serif;background:#f3f5f9;margin:0;padding:20px}'
            . '.sheet{background:#fff;max-width:760px;margin:0 auto;padding:26px 30px;font-size:13.5px}'
            . '@media print{body{background:#fff;padding:0}.sheet{padding:0}}</style></head>'
            . '<body><div class="sheet">' . $report . '</div></body></html>';
    }

    private static function clean(\DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof \DOMComment) {
                $node->removeChild($child);
                continue;
            }
            if (! $child instanceof \DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);
            if (in_array($tag, self::DROPPED_TAGS, true)) {
                $node->removeChild($child);
                continue;
            }

            self::clean($child);

            // Unknown element: keep its content, drop the element
            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }

            foreach (iterator_to_array($child->attributes) as $attribute) {
                if (! self::attributeAllowed($tag, strtolower($attribute->name), trim($attribute->value))) {
                    $child->removeAttribute($attribute->name);
                }
            }

            if ($tag === 'img' && ! $child->hasAttribute('src')) {
                $node->removeChild($child);
            }
        }
    }

    private static function attributeAllowed(string $tag, string $name, string $value): bool
    {
        return match ($name) {
            'src' => $tag === 'img' && preg_match(self::DATA_IMAGE, $value) === 1,
            'href' => $tag === 'a' && preg_match('#^(tel:|mailto:|https://)#i', $value) === 1,
            'style' => preg_match('#(expression|javascript:|url\s*\(|@import|behavior)#i', $value) === 0,
            default => in_array($name, self::ALLOWED_ATTRIBUTES, true),
        };
    }
}
