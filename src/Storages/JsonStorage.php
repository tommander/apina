<?php

declare(strict_types=1);

namespace Apina\Storages;

/**
 * @psalm-import-type ApinaObject from Storage
 * @psalm-import-type ApinaDatabase from Storage
 */
final class JsonStorage extends Storage
{
    public function __construct(
        private string $file
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function load(): bool
    {
        if (!file_exists($this->file)) {
            ($this->logger) && $this->logger->debug('File "' . $this->file . '" does not exist');
            return false;
        }
        $contents = file_get_contents($this->file);
        if (!is_string($contents)) {
            ($this->logger) && $this->logger->debug('File content cannot be read');
            return false;
        }
        $decoded = json_decode($contents, true);
        if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            ($this->logger) && $this->logger->debug('Invalid JSON or error');
            return false;
        }
        if (!$this->verifyDatabase($decoded)) {
            ($this->logger) && $this->logger->debug('Unverified database');
            return false;
        }
        ($this->logger) && $this->logger->debug('Database loaded. Yeeey.');
        $this->setData($decoded);
        return true;
    }

    /** @psalm-assert-if-true ApinaDatabase $input */
    private function verifyDatabase(array $input): bool
    {
        foreach ($input as $key => $value) {
            if (!is_string($key) || empty($key) || !is_array($value)) {
                return false;
            }
            /** @var mixed */
            foreach ($value as $subkey => $subvalue) {
                if (!is_string($subkey) || empty($subkey)) {
                    return false;
                }
            }
        }
        return true;
    }

    #[\Override]
    protected function save(): bool
    {
        $decoded = $this->getDatabase();
        if ($decoded === null) {
            ($this->logger) && $this->logger->debug('JsonStorage save: cannot get database');
        }
        $contents = json_encode($decoded, JSON_PRETTY_PRINT);
        if ($contents === false || json_last_error() !== JSON_ERROR_NONE) {
            ($this->logger) && $this->logger->debug('JsonStorage save: cannot create JSON');
            return false;
        }
        $res = file_put_contents($this->file, $contents);
        if (!is_int($res) || $res === 0) {
            ($this->logger) && $this->logger->debug('JsonStorage save: cannot write storage');
            return false;
        }

        ($this->logger) && $this->logger->debug('JsonStorage save: saved ' . $contents);
        return true;
    }
}
