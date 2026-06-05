<?php

declare(strict_types=1);

namespace Vestige\Http\Error\Format;

use Psr\Http\Message\ResponseInterface;
use Throwable;
use Vestige\Http\Error\FormatRendererInterface;
use Vestige\Http\Problem\Problem;

final readonly class DebugHtmlErrorFormatRenderer implements FormatRendererInterface
{
    public function __construct(private Responder $responder) {}

    /** @return list<string> */
    public function mediaTypes(): array
    {
        return ['text/html'];
    }

    public function render(Problem $problem, Throwable $throwable): ResponseInterface
    {
        $status = $problem->getStatus()->value;

        $title = $this->escape($problem->getTitle());
        $class = $this->escape($throwable::class);
        $message = $this->escape($throwable->getMessage());
        $location = $this->escape($throwable->getFile() . ':' . $throwable->getLine());
        $trace = $this->escape($throwable->getTraceAsString());
        $members = $this->renderMembers($problem);

        $html = <<<HTML
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="utf-8">
                <title>{$status} {$title}</title>
            </head>
            <body>
                <h1>{$status} {$title}</h1>
                {$members}
                <h2>{$class}</h2>
                <p>{$message}</p>
                <p><strong>at</strong> {$location}</p>
                <pre>{$trace}</pre>
            </body>
            </html>
            HTML;

        return $this->responder->respond($status, 'text/html; charset=utf-8', $html);
    }

    private function renderMembers(Problem $problem): string
    {
        $members = [];

        if ($problem->getDetail() !== null) {
            $members['detail'] = $problem->getDetail();
        }

        if ($problem->getInstance() !== null) {
            $members['instance'] = $problem->getInstance();
        }

        foreach ($problem->getExtensions() as $key => $value) {
            if (is_string($value)) {
                $members[$key] = $value;
                continue;
            }
            $members[$key] = (json_encode($value, JSON_INVALID_UTF8_SUBSTITUTE) ?: '');
        }

        if ($members === []) {
            return '';
        }

        $rows = '';
        foreach ($members as $key => $value) {
            $rows .= sprintf('<dt>%s</dt><dd>%s</dd>', $this->escape($key), $this->escape($value));
        }

        return '<dl>' . $rows . '</dl>';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE, 'UTF-8');
    }
}