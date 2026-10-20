<?php
namespace Concrete\Core\Api\Fractal\Transformer;

use Carbon\Carbon;
use Concrete\Core\Api\Resources;
use Concrete\Core\Api\TokenScopes;
use Concrete\Core\Entity\Express\Entity;
use Concrete\Core\Entity\Express\Entry;
use Concrete\Core\Permission\Checker;
use Concrete\Core\Tree\Node\Node;
use Concrete\Core\Tree\Node\Type\ExpressEntrySiteResults;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

class ExpressEntryTransformer extends TransformerAbstract
{


    protected $availableIncludes = [
        'author',
    ];

    /**
     * @var Entity
     */
    protected $object;

    /**
     * @var array<int,int|null> the site of every results node looked up so far
     */
    private $siteIDs = [];

    /**
     * ExpressEntryTransformer constructor.
     * @param string[] $availableIncludes
     */
    public function __construct(Entity $object)
    {
        $this->object = $object;
        foreach ($object->getAttributes() as $attribute) {
            $this->availableIncludes[] = $attribute->getAttributeKeyHandle();
        }
        foreach ($object->getAssociations() as $association) {
            $this->availableIncludes[] = $association->getTargetPropertyName();
        }
    }

    /**
     * @param Entry $entry
     * @return array
     */
    public function transform(Entry $entry)
    {
        if (!$this->isVisible($entry)) {
            return ['id' => $entry->getPublicIdentifier()];
        }

        return [
            'id' => $entry->getPublicIdentifier(),
            'date_added' => Carbon::make($entry->getDateCreated())->toAtomString(),
            'date_last_updated' => $entry->getDateModified() ?
                Carbon::make($entry->getDateModified())->toAtomString() : null,
            'url' => $entry->getURL(),
            'label' => $entry->getLabel(),
            'site_id' => $this->getSiteID($entry),
        ];
    }

    public function includeCustomAttributes(Entry $entry)
    {
        $values = $entry->getObjectAttributeCategory()->getAttributeValues($entry);
        return new Collection($values, new AttributeValueTransformer(), Resources::RESOURCE_CUSTOM_ATTRIBUTES);
    }

    public function includeAuthor(Entry $entry)
    {
        $user = $entry->getAuthor();
        if ($user) {
            return new Item($user->getUserInfoObject(), new UserTransformer(), Resources::RESOURCE_USERS);
        }
    }

    public function __call($method, $arguments)
    {
        $attributeOrObjectHandle = snake_case(substr($method, 7));
        $entry = $arguments[0];
        /**
         * @var $entry Entry
         */
        foreach ($entry->getEntity()->getAttributes() as $attribute) {
            if ($attribute->getAttributeKeyHandle() == $attributeOrObjectHandle) {
                $value = $entry->getAttributeValue($attribute);
                if ($value) {
                    return new Item($value, new AttributeValueTransformer(), Resources::RESOURCE_CUSTOM_ATTRIBUTES);
                }
            }
        }
        $associations = $entry->getAssociations();
        foreach ($associations as $association) {
            if ($association->getAssociation()->getTargetPropertyName() == $attributeOrObjectHandle) {
                if ($association instanceof Entry\OneAssociation) {
                    $associatedEntry = $association->getSelectedEntry();
                    return new Item(
                        $associatedEntry,
                        new ExpressEntryTransformer($associatedEntry->getEntity()),
                        $associatedEntry->getEntity()->getPluralHandle()
                    );
                } else if ($association instanceof Entry\ManyAssociation) {
                    $associatedEntries = $association->getSelectedEntries();
                    return new Collection(
                        $associatedEntries,
                        new ExpressEntryTransformer($association->getAssociation()->getTargetEntity()),
                        $association->getAssociation()->getTargetEntity()->getHandle(),
                    );
                }
            }
        }
    }

    /**
     * Whether the request may see the whole of an entry: the scopes of its token settle
     * it first, and then the permissions, the ones the endpoint serving these things asks about.
     */
    protected function isVisible(Entry $entry): bool
    {
        // the entries of every entity are read with a scope of their own, named after it in the plural
        return app(TokenScopes::class)->has((string) $this->object->getPluralHandle() . ':read')
            && (bool) (new Checker($entry))->canViewExpressEntry();
    }

    /**
     * Get the site whose entries this one is kept with, which an entity keeping them apart by site has
     * a node of its own for.
     *
     * @return int|null NULL where the entity keeps the entries of every site together
     */
    private function getSiteID(Entry $entry): ?int
    {
        $nodeID = (int) $entry->getResultsNodeID();
        if ($nodeID === 0) {
            return null;
        }
        if (!array_key_exists($nodeID, $this->siteIDs)) {
            // the entries of a list share their node, so it is looked up once
            $node = Node::getByID($nodeID);
            $this->siteIDs[$nodeID] = $node instanceof ExpressEntrySiteResults ? $node->getSiteID() : null;
        }

        return $this->siteIDs[$nodeID];
    }
}
