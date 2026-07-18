<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

class HtmlSanitizer
{
    protected array $allowedTags = [
        'p', 'br', 'hr', 'span', 'div', 'section',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'strong', 'b', 'em', 'i', 'u', 's', 'sub', 'sup', 'small',
        'ul', 'ol', 'li',
        'a', 'img',
        'blockquote', 'pre', 'code',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'caption',
        'figure', 'figcaption',
    ];

    protected array $allowedAttributes = [
        'a' => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height'],
        'td' => ['colspan', 'rowspan'],
        'th' => ['colspan', 'rowspan'],
        '*' => ['class', 'id', 'dir', 'lang'],
    ];

    protected array $allowedProtocols = ['http', 'https', 'mailto', 'tel'];

    public function clean(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        $previous = libxml_use_internal_errors(true);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $wrapped = '<?xml encoding="UTF-8"><div id="__sanitize_root__">' . $html . '</div>';
        $dom->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $dom->getElementById('__sanitize_root__');
        if (!$root) {
            return '';
        }

        $this->sanitizeNode($root);

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $dom->saveHTML($child);
        }

        return trim($output);
    }

    protected function sanitizeNode(DOMNode $node): void
    {
        if ($node instanceof DOMElement) {
            $tag = strtolower($node->tagName);

            if (!in_array($tag, $this->allowedTags, true)) {
                // Keep children, drop the unsafe wrapper.
                while ($node->firstChild) {
                    $node->parentNode?->insertBefore($node->firstChild, $node);
                }
                $node->parentNode?->removeChild($node);
                return;
            }

            $allowedForTag = array_unique(array_merge(
                $this->allowedAttributes['*'] ?? [],
                $this->allowedAttributes[$tag] ?? []
            ));

            $removeAttrs = [];
            if ($node->hasAttributes()) {
                foreach (iterator_to_array($node->attributes) as $attr) {
                    $name = strtolower($attr->name);
                    $value = trim((string) $attr->value);

                    if (str_starts_with($name, 'on') || !in_array($name, $allowedForTag, true)) {
                        $removeAttrs[] = $attr->name;
                        continue;
                    }

                    if (in_array($name, ['href', 'src'], true) && !$this->isSafeUrl($value)) {
                        $removeAttrs[] = $attr->name;
                        continue;
                    }

                    if ($name === 'target' && $value !== '_blank' && $value !== '_self') {
                        $removeAttrs[] = $attr->name;
                    }
                }
            }

            foreach ($removeAttrs as $attrName) {
                $node->removeAttribute($attrName);
            }

            if ($tag === 'a') {
                if ($node->hasAttribute('target') && $node->getAttribute('target') === '_blank') {
                    $node->setAttribute('rel', 'noopener noreferrer');
                }
            }
        }

        // Copy children first because the list mutates while sanitizing.
        $children = [];
        foreach ($node->childNodes as $child) {
            $children[] = $child;
        }
        foreach ($children as $child) {
            $this->sanitizeNode($child);
        }
    }

    protected function isSafeUrl(string $url): bool
    {
        if ($url === '' || str_starts_with($url, '#')) {
            return true;
        }

        if (str_starts_with($url, '//')) {
            return false;
        }

        $lower = strtolower($url);
        if (str_contains($lower, 'javascript:') || str_contains($lower, 'data:') || str_contains($lower, 'vbscript:')) {
            return false;
        }

        if (preg_match('#^([a-z][a-z0-9+.-]*):#i', $url, $m)) {
            return in_array(strtolower($m[1]), $this->allowedProtocols, true);
        }

        // Relative URLs
        return true;
    }
}
