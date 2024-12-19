<?php

namespace Mmb\KeySchema;

use Closure;

trait HasKeyboards
{

    protected array $schemas = [];

    protected array $headerSchemas = [];

    protected array $footerSchemas = [];

    /**
     * Set/Add schema key
     *
     * If store() is enabled, this values will save to user table and load with next update.
     * Otherwise, this values is not saving and menu key will reload from your codes.
     *
     * @param array|Closure $key
     * @param string        $name
     * @param bool          $fixed
     * @param bool          $exclude
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
     * @param string        $name
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
     * @param string        $name
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
     * @param string        $name
     * @param bool          $exclude
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
     * @param string        $name
     * @param bool          $exclude
     * @return $this
     */
    public function footer(array|Closure $key, string $name = 'main', bool $exclude = false)
    {
        $this->footerSchemas[] = new KeyboardSchema($this, $key, $name, true, $exclude);
        return $this;
    }

}