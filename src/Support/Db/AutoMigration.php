<?php

namespace Mmb\Support\Db;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Database\Schema\ForeignKeyDefinition;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

class AutoMigration
{

    public Blueprint $blueprint;


    protected $table = '';

    protected $defaultColumns = true;

    public function getTable()
    {
        return $this->table;
    }

    /**
     * Run migration
     *
     * @return void
     */
    public function run()
    {
        if(Schema::hasTable($this->getTable()))
        {
            $this->alertTable();
        }
        else
        {
            $this->createTable();
        }
    }

    /**
     * Create table
     *
     * @return void
     */
    public function createTable()
    {
        Schema::create($this->getTable(), function(Blueprint $table)
        {
            $this->callMigration($table);
        });
    }

    /**
     * Alert table
     *
     * @return void
     */
    public function alertTable()
    {
        Schema::table($this->getTable(), function(Blueprint $table)
        {
            $blueprint = new Blueprint($this->getTable(), null, $table->getPrefix());
            $this->callMigration($blueprint);

            $oldColumns = collect(Schema::getColumns($this->getTable()));
            $oldColumnsNames = $oldColumns->pluck('name')->toArray();
            $last = null;
            foreach($blueprint->getColumns() as $column)
            {
                $name = $column->get('name');
                $old = $oldColumns->firstWhere('name', $name);
                if($old)
                {
                    if(@$old['auto_increment'] && $column->get('autoIncrement'))
                    {
                        // Nothing
                    }
                    else
                    {
                        $table->addColumn($name, $column->get('type'), $column->getAttributes())->change();
                    }

                    $oldColumnsNames = array_diff($oldColumnsNames, [$name]);
                }
                else
                {
                    $new = $table->addColumn($name, $column->get('type'), $column->getAttributes());

                    if($last)
                    {
                        $new->after($last);
                    }
                }

                $last = $name;
            }

            foreach($oldColumnsNames as $name)
            {
                $table->dropColumn($name);
            }
        });
    }

    /**
     * Call migration
     *
     * @param Blueprint $table
     * @return void
     */
    public function callMigration(Blueprint $table)
    {
        $this->blueprint = $table;
        if($this->defaultColumns)
        {
            $table->id();
        }

        $this->migrate($table);

        if($this->defaultColumns)
        {
            $table->timestamps();
        }
        unset($this->blueprint);
    }

    public function __call(string $name, array $arguments)
    {
        return $this->blueprint->$name(...$arguments);
    }

    /**
     * Add php enum value
     *
     * @param string $column
     * @param string $class
     * @return ColumnDefinition
     */
    public function enum(string $column, string $class)
    {
        return $this->blueprint->enum($column, array_map(fn($case) => $case->value, $class::cases()));
    }

    /**
     * Add foreign related to a model.
     *
     * @param Model|string $model
     * @param string       $column
     * @return ForeignKeyDefinition
     */
    public function foreignFor(Model|string $model, string $column)
    {
        if(is_string($model))
        {
            $model = app($model);
        }

        return $this->blueprint->foreign($column)
            ->references($model->getKeyName())
            ->on($model->getTable());
    }

    /**
     * Add relation column and foreign key.
     *
     * @param string                    $model
     * @param string|null               $column
     * @param ForeignKeyDefinition|null $foreign
     * @return ColumnDefinition
     */
    public function addRelatedTo(string $model, string $column = null, ?ForeignKeyDefinition &$foreign = null)
    {
        /** @var Model $model */
        $model = app($model);

        $id = $this->blueprint->foreignIdFor($model, $column);
        $foreign = $this->foreignFor($model, $column ?? $id->get('name'));

        return $id;
    }

    /**
     * Add normal relation to a model, with cascadeOnDelete.
     *
     * @param string                    $model
     * @param string|null               $column
     * @param ForeignKeyDefinition|null $foreign
     * @return ColumnDefinition
     */
    public function relatedTo(string $model, string $column = null, ?ForeignKeyDefinition &$foreign = null)
    {
        $id = $this->addRelatedTo($model, $column, $foreign);

        $foreign->cascadeOnDelete();

        return $id;
    }

    /**
     * Add normal relation to a model.
     *
     * @param string                    $model
     * @param string|null               $column
     * @param ForeignKeyDefinition|null $foreign
     * @return ColumnDefinition
     */
    public function depRelatedTo(string $model, string $column = null, ?ForeignKeyDefinition &$foreign = null)
    {
        return $this->addRelatedTo($model, $column, $foreign);
    }

    /**
     * Add nullable relation to a model, with nullOnDelete.
     *
     * @param string                    $model
     * @param string|null               $column
     * @param ForeignKeyDefinition|null $foreign
     * @return ColumnDefinition
     */
    public function nullableRelatedTo(string $model, string $column = null, ?ForeignKeyDefinition &$foreign = null)
    {
        $id = $this->addRelatedTo($model, $column, $foreign);

        $id->nullable();
        $foreign->nullOnDelete();

        return $id;
    }

    /**
     * Add inheritable columns
     *
     * @return void
     */
    public function inheritable()
    {
        $this->blueprint->string('object_type')->nullable();
        $this->blueprint->unsignedBigInteger('object_id')->nullable();
    }

    /**
     * Add extends columns
     *
     * @param string $model
     * @return void
     */
    public function extends(string $model)
    {
        $this->relatedTo($model, 'base_id');
    }

    /**
     * Default migration
     *
     * @return void
     */
    public function migrate(Blueprint $table)
    {
    }

}
