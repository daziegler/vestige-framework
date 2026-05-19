<?php

declare(strict_types=1);

namespace Vestige\Config;

use Vestige\Config\Exceptions\ConfigExceptionInterface;
use Vestige\Config\Exceptions\InvalidKeyPathException;
use Vestige\Config\Exceptions\KeyNotFoundException;

final readonly class Config
{
    private const string KEY_SEGMENT_SEPARATOR = '.';

    /** @param array<string, mixed> $data */
    public function __construct(private array $data) {}

    /**
     * @throws ConfigExceptionInterface
     */
    public function get(string $key): mixed
    {
        $current = $this->data;

        $keySegments = explode(self::KEY_SEGMENT_SEPARATOR, $key);
        foreach ($keySegments as $segment) {
            if (is_array($current) === false) {
                throw InvalidKeyPathException::forNonArrayPath($key);
            }
            if (array_key_exists($segment, $current) === false || $segment === '') {
                throw KeyNotFoundException::forKey($key);
            }
            $current = $current[$segment];
        }

        return $current;
    }

    public function getOr(string $key, mixed $default): mixed
    {
        try {
            return $this->get($key);
        } catch (ConfigExceptionInterface) {
            return $default;
        }
    }
}
