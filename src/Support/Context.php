<?php

declare(strict_types=1);

namespace OptStack\Support;

/**
 * Runtime Context
 *
 * Stores runtime information provided by the host (plugin/theme).
 * This class is immutable after initialization and must NOT call any WordPress functions.
 *
 * Design Principle:
 * - Plugin/Theme is the Host
 * - OptStack is the Guest
 * - The Host injects context at boot time
 */
final class Context
{
    /**
     * Main plugin/theme file path.
     */
    public readonly string $baseFile;

    /**
     * Absolute path to host root directory.
     */
    public readonly string $baseDir;

    /**
     * URL corresponding to baseDir.
     */
    public readonly string $baseUrl;

    /**
     * Version provided by the host.
     */
    public readonly string $version;

    /**
     * Create a new context instance.
     *
     * @param array{file?: string, dir?: string, url?: string, version?: string} $config
     */
    public function __construct(array $config)
    {
        $this->baseFile = $config['file'] ?? '';
        $this->baseDir = $config['dir'] ?? '';
        $this->baseUrl = $config['url'] ?? '';
        $this->version = $config['version'] ?? 'dev';
    }

    /**
     * Get the frontend assets directory path.
     */
    public function getAssetsDir(): string
    {
        return $this->baseDir . 'frontend/dist/';
    }

    /**
     * Get the frontend assets URL.
     */
    public function getAssetsUrl(): string
    {
        return $this->baseUrl . 'frontend/dist/';
    }

    /**
     * Check if a file exists relative to baseDir.
     */
    public function fileExists(string $relativePath): bool
    {
        return file_exists($this->baseDir . $relativePath);
    }

    /**
     * Get absolute path for a relative path.
     */
    public function path(string $relativePath = ''): string
    {
        return $this->baseDir . ltrim($relativePath, '/');
    }

    /**
     * Get URL for a relative path.
     */
    public function url(string $relativePath = ''): string
    {
        return $this->baseUrl . ltrim($relativePath, '/');
    }

    /**
     * Convert context to array for debugging.
     *
     * @return array{baseFile: string, baseDir: string, baseUrl: string, version: string}
     */
    public function toArray(): array
    {
        return [
            'baseFile' => $this->baseFile,
            'baseDir' => $this->baseDir,
            'baseUrl' => $this->baseUrl,
            'version' => $this->version,
        ];
    }
}
