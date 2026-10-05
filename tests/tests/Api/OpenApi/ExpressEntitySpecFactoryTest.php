<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\OpenApi;

use Concrete\Core\Api\OpenApi\Factory\ExpressEntitySpecFactory;
use Concrete\Core\Attribute\DefaultController;
use Concrete\Core\Entity\Attribute\Key\ExpressKey;
use Concrete\Core\Entity\Express\Entity;
use Concrete\Core\Entity\Express\ManyToOneAssociation;
use Concrete\Core\Entity\Express\OneToManyAssociation;
use Concrete\Tests\TestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use OpenApi\Serializer;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The Express entries of an entity published in the API are described by this factory alone, so what
 * it says is all a client knows about them.
 *
 * @see \Concrete\Core\Api\OpenApi\Factory\ExpressEntitySpecFactory
 */
class ExpressEntitySpecFactoryTest extends TestCase
{
    /**
     * @var array<string,array>|null
     */
    private static $paths;

    /**
     * @var array<string,array>|null
     */
    private static $schemas;

    /**
     * @return array<int,string[]>
     */
    public static function provideOperationsAnsweredByTheSerializer(): array
    {
        return [
            ['/ccm/api/1.0/team_members', 'get'],
            ['/ccm/api/1.0/team_members', 'post'],
            ['/ccm/api/1.0/team_members/{uuid}', 'get'],
            ['/ccm/api/1.0/team_members/{uuid}', 'put'],
        ];
    }

    /**
     * @dataProvider provideOperationsAnsweredByTheSerializer
     */
    public function testTheAnswersAreDescribedWrappedInData(string $path, string $method): void
    {
        $schema = $this->getAnswerSchema($path, $method);

        static::assertSame('object', $schema['type']);
        static::assertArrayHasKey('data', $schema['properties']);
    }

    /**
     * The list is walked by the ID of its entries, and hands over where it got to.
     */
    public function testTheListDescribesTheCursorItAnswersWith(): void
    {
        $schema = $this->getAnswerSchema('/ccm/api/1.0/team_members', 'get');

        static::assertSame(
            ['$ref' => '#/components/schemas/IntegerCursorMeta'],
            $schema['properties']['meta'] ?? []
        );
    }

    /**
     * The delete answer is built by hand, so the serializer never wraps it.
     */
    public function testTheDeletedEntryIsDescribedAsItComes(): void
    {
        static::assertSame(
            ['$ref' => '#/components/schemas/DeletedResponse'],
            $this->getAnswerSchema('/ccm/api/1.0/team_members/{uuid}', 'delete')
        );
    }

    /**
     * @return array<int,string[]>
     */
    public static function provideOperationsOnOneEntry(): array
    {
        return [
            ['get'],
            ['put'],
            ['delete'],
        ];
    }

    /**
     * @dataProvider provideOperationsOnOneEntry
     */
    public function testTheEntryIsFoundByTheParameterThePathDeclares(string $method): void
    {
        $pathParameters = [];
        foreach ($this->getPaths()['/ccm/api/1.0/team_members/{uuid}'][$method]['parameters'] as $parameter) {
            if ($parameter['in'] === 'path') {
                $pathParameters[$parameter['name']] = $parameter;
            }
        }

        static::assertSame(['uuid'], array_keys($pathParameters));
        static::assertTrue($pathParameters['uuid']['required']);
    }

    /**
     * Everything the includes parameter asks for is a resource of its own, which the serializer hands
     * over wrapped in a data property.
     */
    public function testEveryIncludeIsDescribedWithTheDataItComesIn(): void
    {
        $includes = $this->getIncludes();
        $properties = $this->getSchemas()['TeamMember']['properties'];

        static::assertSame(['author', 'role', 'deliverables', 'boss', 'reviewed'], $includes);
        foreach ($includes as $include) {
            static::assertArrayHasKey($include, $properties);
            static::assertSame(
                ['data'],
                array_keys($properties[$include]['properties'] ?? []),
                "The {$include} of an entry comes wrapped in a data property of its own."
            );
        }
    }

    /**
     * An entry associated with many others carries them as a list of objects, not as a list of lists.
     */
    public function testTheEntriesOfAToManyIncludeAreDescribedOneByOne(): void
    {
        $include = $this->getSchemas()['TeamMember']['properties']['deliverables']['properties']['data'];

        static::assertSame('array', $include['type']);
        static::assertSame(['$ref' => '#/components/schemas/Project'], $include['items']);
    }

    /**
     * An entry is named by its public identifier in the answers and in the paths, so a write names the
     * entries it associates the same way.
     */
    public function testTheAssociatedEntriesAreWrittenByTheirPublicIdentifier(): void
    {
        $properties = $this->getSchemas()['NewTeamMember']['properties'];

        static::assertSame('string', $properties['boss']['type']);
        static::assertSame('uuid', $properties['boss']['format']);
        static::assertSame('array', $properties['deliverables']['type']);
        static::assertSame(['type' => 'string', 'format' => 'uuid'], $properties['deliverables']['items']);
    }

    /**
     * An entity associated with another one more than once, the way an event has its speakers and its
     * judges, is written under a name per association: the name of the entity would name them all.
     */
    public function testEachAssociationToTheSameEntityIsWrittenOnItsOwn(): void
    {
        $properties = $this->getSchemas()['NewTeamMember']['properties'];

        static::assertSame(['type' => 'string', 'format' => 'uuid'], $properties['deliverables']['items'] ?? []);
        static::assertSame(['type' => 'string', 'format' => 'uuid'], $properties['reviewed']['items'] ?? []);
        static::assertArrayNotHasKey('projects', $properties);
    }

    /**
     * What a client reads under a name it writes under the same name: an attribute is named by its
     * handle, and an entry by the property of the association that holds it.
     */
    public function testEverythingIsWrittenUnderTheNameItIsReadWith(): void
    {
        static::assertSame(
            array_values(array_diff($this->getIncludes(), ['author'])),
            array_keys($this->getSchemas()['NewTeamMember']['properties'])
        );
    }

    /**
     * @return array the schema of what the operation answers with
     */
    private function getAnswerSchema(string $path, string $method): array
    {
        return $this->getPaths()[$path][$method]['responses'][200]['content']['application/json']['schema'];
    }

    /**
     * @return string[] what the list lets the includes parameter ask for
     */
    private function getIncludes(): array
    {
        foreach ($this->getPaths()['/ccm/api/1.0/team_members']['get']['parameters'] as $parameter) {
            if ($parameter['name'] === 'includes') {
                return $parameter['schema']['items']['enum'];
            }
        }

        return [];
    }

    /**
     * @return array<string,array> the paths of an entity with an attribute and an association, as a
     *                             client reads them
     */
    private function getPaths(): array
    {
        if (self::$paths === null) {
            $this->buildSpec();
        }

        return self::$paths;
    }

    /**
     * @return array<string,array>
     */
    private function getSchemas(): array
    {
        if (self::$schemas === null) {
            $this->buildSpec();
        }

        return self::$schemas;
    }

    private function buildSpec(): void
    {
        $fragment = (new ExpressEntitySpecFactory())->build($this->buildEntity());

        self::$paths = [];
        foreach ($fragment->getPaths()->getPathsAsPathItems() as $pathItem) {
            self::$paths[$pathItem->path] = json_decode(json_encode($pathItem), true);
        }
        $components = (new Serializer())->deserialize(
            json_encode($fragment->getComponents()),
            'OpenApi\Annotations\Components'
        );
        self::$schemas = json_decode(json_encode($components), true)['schemas'];
    }

    private function buildEntity(): Entity
    {
        $target = new Entity();
        $target->setHandle('project');
        $target->setPluralHandle('projects');
        $target->setName('Project');

        $association = new OneToManyAssociation();
        $association->setTargetEntity($target);
        $association->setTargetPropertyName('deliverables');

        $manager = new Entity();
        $manager->setHandle('manager');
        $manager->setPluralHandle('managers');
        $manager->setName('Manager');

        $toOne = new ManyToOneAssociation();
        $toOne->setTargetEntity($manager);
        $toOne->setTargetPropertyName('boss');

        // a second association to the entity of the first one, the way an event has speakers and judges
        $reviewed = new OneToManyAssociation();
        $reviewed->setTargetEntity($target);
        $reviewed->setTargetPropertyName('reviewed');

        $attribute = $this->createMock(ExpressKey::class);
        $attribute->method('getAttributeKeyHandle')->willReturn('role');
        $attribute->method('getAttributeKeyDisplayName')->willReturn('Role');
        // a text attribute, the kind whose values the API describes on its own
        $attribute->method('getController')->willReturn(new DefaultController($this->createMock(EntityManager::class)));

        $entity = new Entity();
        $entity->setHandle('team_member');
        $entity->setPluralHandle('team_members');
        $entity->setName('Team Member');
        $entity->setAttributes(new ArrayCollection([$attribute]));
        $entity->setAssociations(new ArrayCollection([$association, $toOne, $reviewed]));

        return $entity;
    }
}
