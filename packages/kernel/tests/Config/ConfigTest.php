<?php

declare(strict_types=1);

namespace Vestige\Tests\Config;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vestige\Config\Config;
use Vestige\Config\Exceptions\InvalidKeyPathException;
use Vestige\Config\Exceptions\KeyNotFoundException;

#[CoversClass(Config::class)]
#[CoversClass(KeyNotFoundException::class)]
#[CoversClass(InvalidKeyPathException::class)]
final class ConfigTest extends TestCase
{
    #[Test]
    public function it_returns_top_level_value(): void
    {
        $config = new Config(['app' => 'vestige']);

        self::assertSame('vestige', $config->get('app'));
    }

    #[Test]
    public function it_throws_when_key_missing(): void
    {
        $config = new Config([]);

        $this->expectException(KeyNotFoundException::class);
        $config->get('missing');
    }

    #[Test]
    public function get_or_returns_default_when_key_missing(): void
    {
        $config = new Config([]);

        self::assertSame('fallback', $config->getOr('missing', 'fallback'));
    }

    #[Test]
    public function get_or_returns_value_when_key_present(): void
    {
        $config = new Config(['app' => 'vestige']);

        self::assertSame('vestige', $config->getOr('app', 'fallback'));
    }

    #[Test]
    public function it_traverses_dot_notation(): void
    {
        $config = new Config(['app' => ['name' => 'vestige']]);

        self::assertSame('vestige', $config->get('app.name'));
    }

    #[Test]
    public function get_or_returns_default_when_nested_key_missing(): void
    {
        $config = new Config(['app' => ['name' => 'vestige']]);

        self::assertSame('fallback', $config->getOr('app.missing', 'fallback'));
    }

    #[Test]
    public function it_throws_when_path_descends_into_non_array(): void
    {
        $config = new Config(['app' => 'vestige']);

        $this->expectException(InvalidKeyPathException::class);
        $config->get('app.name');
    }

    #[Test]
    public function get_or_returns_default_on_invalid_path(): void
    {
        $config = new Config(['app' => 'vestige']);

        self::assertSame('fallback', $config->getOr('app.name', 'fallback'));
    }

    #[Test]
    public function it_traverses_arbitrary_depth(): void
    {
        $config = new Config(
            [
                'a' => ['b' => ['c' => ['d' => ['e' => 'deep']]]],
            ]
        );

        self::assertSame('deep', $config->get('a.b.c.d.e'));
    }

    #[Test]
    public function it_throws_when_key_is_empty(): void
    {
        $config = new Config(['app' => 'vestige']);

        $this->expectException(KeyNotFoundException::class);
        $config->get('');
    }

    #[Test]
    public function it_throws_when_key_ends_with_dot(): void
    {
        $config = new Config(['app' => ['name' => 'vestige']]);

        $this->expectException(KeyNotFoundException::class);
        $config->get('app.');
    }
}
