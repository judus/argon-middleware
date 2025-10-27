<?php

declare(strict_types=1);

namespace Tests\Unit;

use Maduser\Argon\Middleware\Support\Html;
use PHPUnit\Framework\TestCase;

final class HtmlTest extends TestCase
{
    public function testToHtmlReplacesPlaceholders(): void
    {
        $html = Html::create('Hello {{ name }}!', ['name' => 'World']);

        self::assertSame('Hello World!', $html->toHtml());
    }

    public function testToHtmlCachesRenderedOutput(): void
    {
        $html = Html::create('Value: {{ number }}', ['number' => 5]);

        $first = $html->toHtml();
        $second = $html->toHtml();

        self::assertSame($first, $second);
    }

    public function testToStringDelegatesToHtml(): void
    {
        $html = Html::create('<p>{{ text }}</p>', ['text' => 'Argon']);

        self::assertSame('<p>Argon</p>', (string) $html);
    }
}
