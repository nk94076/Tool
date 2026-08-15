<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class EmailTemplate extends Model
{
    protected string $table = 'email_templates';

    public function findBySlug(string $slug): ?array
    {
        return $this->whereFirst('slug', $slug);
    }

    /**
     * Replace {{variable}} placeholders. Values are HTML-escaped to avoid
     * template injection when a variable originates from user-supplied data
     * (e.g. full name).
     */
    public function render(array $template, array $vars): array
    {
        $replace = function (string $text) use ($vars): string {
            foreach ($vars as $key => $value) {
                $text = str_replace('{{' . $key . '}}', htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'), $text);
            }
            return $text;
        };
        return [
            'subject' => $replace($template['subject']),
            'body_html' => $replace($template['body_html']),
        ];
    }
}
