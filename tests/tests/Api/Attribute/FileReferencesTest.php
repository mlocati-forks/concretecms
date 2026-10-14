<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Attribute;

use Concrete\Attribute\ImageFile\Controller as ImageFileController;
use Concrete\Attribute\Textarea\Controller as TextareaController;
use Concrete\Core\Api\Attribute\AttributeApiHandler;
use Concrete\Core\Attribute\Key\Category;
use Concrete\Core\Attribute\Key\FileKey;
use Concrete\Core\Attribute\Type as AttributeType;
use Concrete\Core\Cache\CacheLocal;
use Concrete\Core\Entity\Attribute\Key\Key;
use Concrete\Core\Entity\Attribute\Key\Settings\TextareaSettings;
use Concrete\Core\Entity\Attribute\Value\Value\TextValue;
use Concrete\Core\Entity\File\File as FileEntity;
use Concrete\Core\File\Import\FileImporter;
use Concrete\TestHelpers\File\FileStorageTestCase;
use Doctrine\ORM\EntityManager;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * A value of an attribute names a file by UUID, the way the value of a block does: what a client
 * reads of the one goes into the other without being taken apart.
 *
 * @see \Concrete\Attribute\ImageFile\Api
 * @see \Concrete\Attribute\Textarea\Api
 */
class FileReferencesTest extends FileStorageTestCase
{
    /**
     * @var \Concrete\Core\Entity\File\File|null
     */
    private $file;

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\TestHelpers\Database\ConcreteDatabaseTestCase::getTables()
     */
    protected function getTables()
    {
        return array_merge(parent::getTables(), [
            'ConfigStore',
            'FilePermissionAssignments',
            'FileVersionLog',
            'Logs',
            'PermissionAccessEntityTypes',
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\TestHelpers\Database\ConcreteDatabaseTestCase::getEntityClassNames()
     */
    protected function getEntityClassNames(): array
    {
        return array_merge(parent::getEntityClassNames(), [
            \Concrete\Core\Entity\Attribute\Category::class,
            \Concrete\Core\Entity\Attribute\Key\FileKey::class,
            \Concrete\Core\Entity\Attribute\Key\Key::class,
            \Concrete\Core\Entity\Attribute\Key\Settings\EmptySettings::class,
            \Concrete\Core\Entity\Attribute\Key\Settings\Settings::class,
            \Concrete\Core\Entity\Attribute\Type::class,
            \Concrete\Core\Entity\Attribute\Value\FileValue::class,
            \Concrete\Core\Entity\Attribute\Value\Value\NumberValue::class,
            \Concrete\Core\Entity\Attribute\Value\Value\Value::class,
            \Concrete\Core\Entity\File\File::class,
            \Concrete\Core\Entity\File\Version::class,
        ]);
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->file = null;
        static::getStorageLocation();
        // importing an image inspects it, and its size goes into the attributes of the file
        if (Category::getByHandle('file') === null) {
            Category::add('file');
            $number = AttributeType::add('number', 'Number');
            FileKey::add($number, ['akHandle' => 'width', 'akName' => 'Width']);
            FileKey::add($number, ['akHandle' => 'height', 'akName' => 'Height']);
        }
        CacheLocal::flush();
    }

    public function testAFileIsNamedByItsUuid(): void
    {
        $file = $this->getFile();

        $value = $this->createImageFileHandler()->createApiValue($file->getFileUUID());

        static::assertSame($file->getFileID(), $value->getValue()->getFileID());
    }

    public function testTheFileAReadHandedOverGoesBackAsItCame(): void
    {
        $file = $this->getFile();
        $read = ['data' => ['id' => $file->getFileID(), 'uuid' => $file->getFileUUID()]];

        $value = $this->createImageFileHandler()->createApiValue($read);

        static::assertSame($file->getFileID(), $value->getValue()->getFileID());
    }

    /**
     * The UUID is there to keep the IDs of the site to itself, as it is for a block.
     */
    public function testAFileWithAUuidIsNotNamedByItsId(): void
    {
        $value = $this->createImageFileHandler()->createApiValue($this->getFile()->getFileID());

        static::assertNull($value->getValue());
    }

    public function testNamingNoFileEmptiesTheAttribute(): void
    {
        $value = $this->createImageFileHandler()->createApiValue(null);

        static::assertNull($value->getValue());
    }

    public function testNamingNothingOfTheKindLeavesTheAttributeAlone(): void
    {
        static::assertNull($this->createImageFileHandler()->createApiValue(['something' => 'else']));
    }

    public function testARichTextNamesTheFilesItRefersToByUuid(): void
    {
        $file = $this->getFile();
        $html = '<p>{CCM:FID_' . $file->getFileID() . '} and {CCM:FID_DL_' . $file->getFileID() . '}</p>'
            . '<concrete-picture fID="' . $file->getFileID() . '" />';

        $value = $this->createTextareaHandler(TextareaController::MODE_RICHTEXT, $html)->getApiValue();

        static::assertStringContainsString('{CCM:FID_' . $file->getFileUUID() . '}', $value);
        static::assertStringContainsString('{CCM:FID_DL_' . $file->getFileUUID() . '}', $value);
        static::assertStringContainsString('fID="' . $file->getFileUUID() . '"', $value);
    }

    public function testAPlainTextIsHandedOverAsItIs(): void
    {
        $text = '{CCM:FID_' . $this->getFile()->getFileID() . '}';

        static::assertSame($text, $this->createTextareaHandler(TextareaController::MODE_TEXT, $text)->getApiValue());
    }

    public function testAKeyWithNoValueHandsOverNothing(): void
    {
        $handler = $this->createTextareaHandler(TextareaController::MODE_RICHTEXT, null);

        static::assertNull($handler->getApiValue());
    }

    private function createImageFileHandler(): AttributeApiHandler
    {
        return (new ImageFileController($this->createMock(EntityManager::class)))->getApiHandler();
    }

    /**
     * @param string|null $value the text the key holds, NULL where it holds none
     */
    private function createTextareaHandler(string $mode, ?string $value): AttributeApiHandler
    {
        $settings = $this->createMock(TextareaSettings::class);
        $settings->method('getMode')->willReturn($mode);
        $key = $this->createMock(Key::class);
        $key->method('getAttributeKeySettings')->willReturn($settings);
        $controller = new TextareaController($this->createMock(EntityManager::class));
        $controller->setAttributeKey($key);
        if ($value !== null) {
            $text = new TextValue();
            $text->setValue($value);
            $controller->setAttributeValue($text);
        }

        return $controller->getApiHandler();
    }

    private function getFile(): FileEntity
    {
        if ($this->file === null) {
            $version = app(FileImporter::class)->importLocalFile(
                DIR_TESTS . '/assets/File/StorageLocation/tiny.png',
                'tiny.png'
            );
            $this->file = $version->getFile();
        }

        return $this->file;
    }
}
