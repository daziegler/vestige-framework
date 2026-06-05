<?php

declare(strict_types=1);

namespace Vestige\Config;

use FilesystemIterator;
use SplFileInfo;
use Vestige\Config\Exceptions\ConfigExceptionInterface;
use Vestige\Config\Exceptions\DirectoryNotFoundException;
use Vestige\Config\Exceptions\InvalidConfigFileException;
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
    public static function fromDirectory(string $path): self
    {
        if (is_dir($path) === false) {
            throw DirectoryNotFoundException::forPath($path);
        }

        $data = [];
        foreach (new FilesystemIterator($path) as $file) {
            /** @var SplFileInfo $file */
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $loaded = require $file->getPathname();
            if (is_array($loaded) === false) {
                throw InvalidConfigFileException::forNonArrayReturn($file->getPathname());
            }

            $data[$file->getBasename('.php')] = $loaded;
        }

        return new self($data);
    }

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
