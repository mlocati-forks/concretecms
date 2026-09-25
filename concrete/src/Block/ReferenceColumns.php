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
 * @see \Concrete\Core\Block\BlockController::getReferenceColumns()
 */
final class ReferenceColumns
{
    /**
     * A column holding the ID of a page.
     *
     * @var string
     */
    public const PAGE = 'page';

    /**
     * A column holding the ID of a file.
     *
     * @var string
     */
    public const FILE = 'file';

    /**
     * A column holding the ID of a page type.
     *
     * @var string
     */
    public const PAGE_TYPE = 'pagetype';

    /**
     * A column holding the ID of an RSS feed of pages.
     *
     * @var string
     */
    public const PAGE_FEED = 'pagefeed';

    /**
     * A column holding the ID of a folder of files.
     *
     * @var string
     */
    public const FILE_FOLDER = 'filefolder';

    /**
     * A column holding HTML, which may refer to pages and files of the site.
     *
     * @var string
     */
    public const CONTENT = 'content';

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
     * Get the kinds of reference that at least one column holds.
     *
     * @return string[] the ReferenceColumns::... constants that have been declared
     */
    public function getReferences(): array
    {
        return array_keys($this->columns);
    }

    /**
     * Get the names of the columns holding a kind of reference.
     *
     * @param string $reference one of the ReferenceColumns::... constants
     *
     * @return string[]
     */
    public function getColumns(string $reference): array
    {
        return $this->columns[$reference] ?? [];
    }

    /**
     * Get the kind of reference that a column holds.
     *
     * @return string one of the ReferenceColumns::... constants, or an empty string when the column holds a value of its own
     */
    public function getReference(string $column): string
    {
        return $this->references[strtolower($column)] ?? '';
    }
}
