<?php

declare(strict_types=1);

namespace Concrete\Core\Block;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The columns of a block type that don't hold a value of their own, but a reference to something
 * else of the site: everything else is described by the db.xml file of the block type.
 *
 * @readonly
 *
 * @see \Concrete\Core\Reference\ReferenceKind
 * @see \Concrete\Core\Block\BlockController::getReferenceColumns()
 */
final class ReferenceColumns
{
    /**
     * The declared columns, by the kind of reference they hold.
     *
     * @var array<string,string[]>
     */
    private $columns;

    /**
     * The kind of reference of every declared column, by its lower-cased name.
     *
     * @var array<string,string>
     */
    private $references;

    /**
     * @param array<string,string[]> $columns the declared columns, by the kind of reference they hold (a column declared more than once keeps the kind declared first)
     */
    public function __construct(array $columns)
    {
        $this->columns = [];
        $this->references = [];
        foreach ($columns as $reference => $names) {
            $names = array_values(array_filter(array_map('strval', (array) $names), static function (string $name): bool {
                return $name !== '';
            }));
            if ($names === []) {
                continue;
            }
            $this->columns[(string) $reference] = $names;
            foreach ($names as $name) {
                $key = strtolower($name);
                if (!isset($this->references[$key])) {
                    $this->references[$key] = (string) $reference;
                }
            }
        }
    }

    /**
     * Get a copy of these columns with some more declared, which is how a block type adds to what
     * its $btExport... properties say.
     *
     * @param array<string,string[]> $columns more declared columns, by the kind of reference they hold
     */
    public function with(array $columns): self
    {
        $merged = $this->columns;
        foreach ($columns as $reference => $names) {
            $merged[(string) $reference] = array_merge($merged[(string) $reference] ?? [], (array) $names);
        }

        return new self($merged);
    }

    /**
     * @return string[] the \Concrete\Core\Reference\ReferenceKind::... constants that have been declared
     */
    public function getReferences(): array
    {
        return array_keys($this->columns);
    }

    /**
     * @param string $reference one of the \Concrete\Core\Reference\ReferenceKind::... constants
     * @return string[]
     */
    public function getColumns(string $reference): array
    {
        return $this->columns[$reference] ?? [];
    }

    /**
     * @return string one of the \Concrete\Core\Reference\ReferenceKind::... constants, or an empty string when the column holds a value of its own
     */
    public function getReference(string $column): string
    {
        return $this->references[strtolower($column)] ?? '';
    }
}
