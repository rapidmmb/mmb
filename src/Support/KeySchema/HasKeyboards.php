<?php

namespace Mmb\Support\KeySchema;

use Closure;
use Mmb\Core\Updates\Update;
use Mmb\Support\Action\ActionCallback;
use PHPUnit\Framework\Assert;

trait HasKeyboards
{

    /**
     * @var KeyboardSchema[]
     */
    protected array $schemas = [];

    /**
     * @var KeyboardSchema[]
     */
    protected array $headerSchemas = [];

    /**
     * @var KeyboardSchema[]
     */
    protected array $footerSchemas = [];

    /**
     * Set/Add schema key
     *
     * If store() is enabled, this values will save to user table and load with next update.
     * Otherwise, this values is not saving and menu key will reload from your codes.
     *
     * @param array|Closure $key
     * @param string $name
     * @param bool $fixed
     * @param bool $exclude
     * @return $this
     */
    public function schema(array|Closure $key, string $name = 'main', bool $fixed = false, bool $exclude = false)
    {
        $this->schemas[] = new KeyboardSchema($this, $key, $name, $fixed, $exclude);
        return $this;
    }

    /**
     * Set/Add schema with fixed keys
     *
     * @param array|Closure $key
     * @param string $name
     * @return $this
     */
    public function schemaFixed(array|Closure $key, string $name = 'main')
    {
        return $this->schema($key, $name, true);
    }

    /**
     * Set/Add schema that not included in loading.
     * These keys can't respond, just for displaying
     *
     * @param array|Closure $key
     * @param string $name
     * @return $this
     */
    public function schemaExcluded(array|Closure $key, string $name = 'main')
    {
        return $this->schema($key, $name, exclude: true);
    }

    /**
     * Set schema header key
     *
     * This values always load from your codes.
     *
     * @param array|Closure $key
     * @param string $name
     * @param bool $exclude
     * @return $this
     */
    public function header(array|Closure $key, string $name = 'main', bool $exclude = false)
    {
        $this->headerSchemas[] = new KeyboardSchema($this, $key, $name, true, $exclude);
        return $this;
    }

    /**
     * Set schema footer key
     *
     * This values always load from your codes.
     *
     * @param array|Closure $key
     * @param string $name
     * @param bool $exclude
     * @return $this
     */
    public function footer(array|Closure $key, string $name = 'main', bool $exclude = false)
    {
        $this->footerSchemas[] = new KeyboardSchema($this, $key, $name, true, $exclude);
        return $this;
    }

    protected array $rawKey;
    protected array $keyDataActionMap;
    protected array $storableKeyMap;

    protected function makeReadyKeyboards(bool $isCreating = true, bool $storeEnabled = true)
    {
        $this->rawKey = [];
        $this->keyDataActionMap = [];

        if ($storeEnabled) {
            $this->storableKeyMap = [];
        }

        $schemas = [
            ...$this->headerSchemas,
            ...$this->schemas,
            ...$this->footerSchemas,
        ];

        /** @var KeyboardSchema $schema */
        foreach ($schemas as $schema) {

            $storable = $storeEnabled && !$schema->fixed && !$schema->exclude;

            // If storable, skip
//            if ($storable && $skipStorable) {
//                continue;
//            }

            // Loading mode & Excluded groups
            if (!$isCreating && $schema->exclude) {
                continue;
            }

            [$rawKey0, $keyDataActionMap0, $storableKeyMap0] = $schema->normalizeKey($storable);

            array_push($this->rawKey, ...$rawKey0);

            $this->keyDataActionMap = array_merge($this->keyDataActionMap, $keyDataActionMap0);

            if ($storable) {
                $this->storableKeyMap = array_merge($this->storableKeyMap, $storableKeyMap0);
            }

        }
    }

    protected function loadKeyboards(bool $storable = false, array $storableKeyMap = [])
    {
        $this->keyDataActionMap = [];

        if ($storable) {
            $this->storableKeyMap = $storableKeyMap;

            foreach ($this->storableKeyMap as $data => $action) {
                if ($action = $this->restoreActionCallback($action)) {
                    $this->keyDataActionMap[$data] = $action;
                }
            }
        }

        $schemas = [
            ...$this->headerSchemas,
            ...$this->schemas,
            ...$this->footerSchemas,
        ];

        /** @var KeyboardSchema $schema */
        foreach ($schemas as $schema) {

            if ($schema->exclude || ($storable && !$schema->fixed)) {
                continue;
            }

            [, $keyDataMap0] = $schema->normalizeKey();

            $this->keyDataActionMap = array_merge($this->keyDataActionMap, $keyDataMap0);

        }
    }

    protected function detectClickedKeyData(Update $update): ?string
    {
        return KeyUniqueData::fromUpdate($update);
    }

    protected function findKeyActionUsingData(string $data): ?ActionCallback
    {
        if (!isset($this->keyDataActionMap)) {
            $this->makeReadyKeyboards(false);
        }

        return $this->keyDataActionMap[$data] ?? null;
    }

    public function findClickedKeyAction(Update $update): ?ActionCallback
    {
        if (null === $uniqueData = $this->detectClickedKeyData($update)) {
            return null;
        }

        return $this->findKeyActionUsingData($uniqueData);
    }

    public function toKeyboardArray(): array
    {
        if (!isset($this->rawKey)) {
            throw new \InvalidArgumentException("Try to get not ready keyboards");
        }

        return $this->rawKey;
    }

    public function toStorableKeyMap(): array
    {
        if (!isset($this->storableKeyMap)) {
            throw new \InvalidArgumentException("Try to get not ready keyboards");
        }

        return $this->storableKeyMap;
    }


    public function assertKeyboardArray(array $expected, string $message = '')
    {
        Assert::assertSame($expected, $this->toKeyboardArray(), $message);
    }

    public function assertKeyDataActionMap(array $expected, string $message = '')
    {
        Assert::assertSame($expected, $this->keyDataActionMap, $message);
    }

    public function assertStorableKeyMap(array $expected, string $message = '')
    {
        Assert::assertSame($expected, $this->storableKeyMap, $message);
    }

}