<?php

declare(strict_types=1);

namespace Vestige\Tests\Session;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Vestige\Session\SessionId;

#[CoversClass(SessionId::class)]
final class SessionIdTest extends TestCase
{
    #[Test]
    public function generate_produces_32_lowercase_hex_chars(): void
    {
        $id = SessionId::generate();

        self::assertMatchesRegularExpression('/^[a-f0-9]{32}\z/', (string) $id);
    }

    #[Test]
    public function generate_produces_unique_ids(): void
    {
        self::assertNotSame((string) SessionId::generate(), (string) SessionId::generate());
    }

    #[Test]
    public function try_from_accepts_a_valid_id(): void
    {
        $id = SessionId::tryFrom('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa');

        self::assertNotNull($id);
        self::assertSame('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa', (string) $id);
    }

    /** @return iterable<string, array{string}> */
    public static function invalidIds(): iterable
    {
        yield 'traversal' => ['../../etc/passwd'];
        yield 'uppercase' => ['AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'];
        yield 'too short' => ['abc123'];
        yield 'too long' => ['aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa'];
        yield 'trailing newline' => ["aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa\n"];
        yield 'empty' => [''];
    }

    #[Test]
    #[DataProvider('invalidIds')]
    public function try_from_rejects_invalid_input(string $invalid): void
    {
        self::assertNull(SessionId::tryFrom($invalid));
    }
}