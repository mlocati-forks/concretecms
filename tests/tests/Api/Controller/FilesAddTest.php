<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Controller;

use Concrete\Core\Api\Controller\Files;
use Concrete\Core\Entity\File\File;
use Concrete\Core\File\Incoming;
use Concrete\Core\Http\Request;
use Concrete\Core\Permission\Category as PermissionCategory;
use Concrete\Core\Permission\Key\Key as PermissionKey;
use Concrete\Core\User\User;
use Concrete\TestHelpers\File\FileStorageTestCase;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use League\Fractal\Resource\Item;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The ways a file is added to the file manager: an upload, the contents of the file, an URL, or the name
 * of a file the administrators of the site have put in its incoming directory.
 *
 * @see \Concrete\Core\Api\Controller\Files::add()
 */
class FilesAddTest extends FileStorageTestCase
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\TestHelpers\Database\ConcreteDatabaseTestCase::getTables()
     */
    protected function getTables()
    {
        return array_merge(parent::getTables(), [
            'FileVersionLog',
            'Logs',
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
            'Concrete\Core\Entity\Attribute\Category',
            'Concrete\Core\Entity\Attribute\Key\Key',
            'Concrete\Core\Entity\Attribute\Key\FileKey',
            'Concrete\Core\Entity\Attribute\Key\Settings\Settings',
            'Concrete\Core\Entity\Attribute\Key\Settings\EmptySettings',
            'Concrete\Core\Entity\Attribute\Type',
            'Concrete\Core\Entity\Attribute\Value\FileValue',
            'Concrete\Core\Entity\Attribute\Value\Value\Value',
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\TestHelpers\File\FileStorageTestCase::setUp()
     */
    public function setUp(): void
    {
        parent::setUp();
        // with no permission key registered, only the superuser is allowed anything
        $superUser = $this->createMock(User::class);
        $superUser->method('isSuperUser')->willReturn(true);
        app()->instance(User::class, $superUser);
        // the extensions a folder takes are read from the key itself, which the superuser does not skip
        if (PermissionCategory::getByHandle('file_folder') === null) {
            PermissionCategory::add('file_folder');
            PermissionKey::add('file_folder', 'add_file', 'Add File', '', 0, 1);
        }
        static::getStorageLocation();
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\TestHelpers\File\FileStorageTestCase::tearDown()
     */
    public function tearDown(): void
    {
        app()->forgetInstance(User::class);
        app()->forgetInstance(Incoming::class);
        parent::tearDown();
    }

    public function testAFileOfTheIncomingDirectoryIsAdded(): void
    {
        $this->putInIncoming('holiday.txt', 'the contents');

        $answer = $this->add(['incoming_filename' => 'holiday.txt']);

        $file = $this->added($answer);
        static::assertSame('holiday.txt', $file->getFileName());
        static::assertSame('the contents', $file->getFileContents());
        // the dialog of the file manager leaves it there as well, unless it is told otherwise
        static::assertSame(['holiday.txt'], $this->incomingFilenames());
    }

    public function testAFileOfTheIncomingDirectoryIsTakenAwayWhenAsked(): void
    {
        $this->putInIncoming('holiday.txt', 'the contents');

        $answer = $this->add(['incoming_filename' => 'holiday.txt', 'remove_from_incoming' => true]);

        static::assertSame('holiday.txt', $this->added($answer)->getFileName());
        static::assertSame([], $this->incomingFilenames());
        static::assertSame(['removed_from_incoming' => true], $answer->getMeta());
    }

    /**
     * The answer says nothing of the incoming directory to whoever asked nothing of it.
     */
    public function testAFileLeftInTheIncomingDirectoryIsNotReportedOn(): void
    {
        $this->putInIncoming('holiday.txt', 'the contents');

        $answer = $this->add(['incoming_filename' => 'holiday.txt']);

        static::assertSame('holiday.txt', $this->added($answer)->getFileName());
        static::assertSame([], $answer->getMeta());
    }

    /**
     * A directory that will not let go of its copy does not cost the client the file it has just added:
     * the answer carries the file, and says the copy is still there.
     */
    public function testAFileThatCannotBeTakenAwayIsAddedAllTheSame(): void
    {
        $this->putInIncoming('holiday.txt', 'the contents');
        $this->refuseToDeleteFromIncoming();

        $answer = $this->add(['incoming_filename' => 'holiday.txt', 'remove_from_incoming' => true]);

        static::assertSame('holiday.txt', $this->added($answer)->getFileName());
        static::assertSame(['removed_from_incoming' => false], $answer->getMeta());
    }

    /**
     * Whoever took the file away in the meantime did what the request asked for.
     */
    public function testAFileThatIsNoLongerThereCountsAsTakenAway(): void
    {
        $this->putInIncoming('holiday.txt', 'the contents');
        $this->deleteFromIncomingBehindItsBack();

        $answer = $this->add(['incoming_filename' => 'holiday.txt', 'remove_from_incoming' => true]);

        static::assertSame('holiday.txt', $this->added($answer)->getFileName());
        static::assertSame(['removed_from_incoming' => true], $answer->getMeta());
    }

    public function testAFileOfTheIncomingDirectoryTakesTheNameTheRequestGivesIt(): void
    {
        $this->putInIncoming('holiday.txt', 'the contents');

        $answer = $this->add(['incoming_filename' => 'holiday.txt', 'filename' => 'summer.txt']);

        static::assertSame('summer.txt', $this->added($answer)->getFileName());
        static::assertSame(['holiday.txt'], $this->incomingFilenames());
    }

    public function testANameTheIncomingDirectoryHasNotIsRefused(): void
    {
        $this->putInIncoming('holiday.txt', 'the contents');

        $answer = $this->add(['incoming_filename' => 'winter.txt']);

        static::assertInstanceOf(JsonResponse::class, $answer);
        static::assertSame(400, $answer->getStatusCode());
    }

    /**
     * A client names a file of that directory, and nothing else: a path leads nowhere, even to a file that
     * is there.
     */
    public function testAPathIsNotAName(): void
    {
        $this->putInIncoming('holiday.txt', 'the contents');

        foreach (['./holiday.txt', 'sub/holiday.txt', '../holiday.txt'] as $path) {
            $answer = $this->add(['incoming_filename' => $path]);

            static::assertInstanceOf(JsonResponse::class, $answer, $path . ' is not a name of the directory');
            static::assertSame(400, $answer->getStatusCode(), $path . ' is not a name of the directory');
        }
    }

    public function testAnExtensionThisSiteRefusesIsRefused(): void
    {
        $this->putInIncoming('holiday.exe', 'the contents');

        $answer = $this->add(['incoming_filename' => 'holiday.exe']);

        static::assertInstanceOf(JsonResponse::class, $answer);
        static::assertSame(400, $answer->getStatusCode());
        static::assertSame(['holiday.exe'], $this->incomingFilenames());
    }

    public function testARequestThatSendsNoFileAtAllIsRefused(): void
    {
        $answer = $this->add([]);

        static::assertInstanceOf(JsonResponse::class, $answer);
        static::assertSame(400, $answer->getStatusCode());
    }

    /**
     * A request that names two ways of sending the file is a request nobody can answer: the file it means
     * is anybody's guess.
     */
    public function testARequestThatAsksForTwoWaysAtOnceIsRefused(): void
    {
        $this->putInIncoming('holiday.txt', 'the contents');

        $answer = $this->add([
            'incoming_filename' => 'holiday.txt',
            'url' => 'https://www.example.com/holiday.txt',
        ]);

        static::assertInstanceOf(JsonResponse::class, $answer);
        static::assertSame(400, $answer->getStatusCode());
        // nothing of either way was done
        static::assertSame(['holiday.txt'], $this->incomingFilenames());
    }

    public function testAnUploadedFileIsAdded(): void
    {
        $answer = $this->upload('holiday.txt', []);

        static::assertSame('holiday.txt', $this->added($answer)->getFileName());
    }

    /**
     * An uploaded file is sent with its own name, so a request that also asks for another one is asking
     * for something nobody reads: it is refused instead of being guessed.
     */
    public function testAnUploadThatAlsoNamesTheFileIsRefused(): void
    {
        $answer = $this->upload('holiday.txt', ['filename' => 'summer.txt']);

        static::assertInstanceOf(JsonResponse::class, $answer);
        static::assertSame(400, $answer->getStatusCode());
    }

    /**
     * @param array<string,mixed> $body
     *
     * @return \League\Fractal\Resource\Item|\Symfony\Component\HttpFoundation\JsonResponse
     */
    private function upload(string $filename, array $body)
    {
        $localFilename = CCM_TESTS_TEMPDIR . '/' . $filename;
        file_put_contents($localFilename, 'the contents');
        $uploadedFile = new UploadedFile($localFilename, $filename, null, null, true);
        $request = Request::create('/ccm/api/1.0/files', 'POST', $body, [], ['file' => $uploadedFile]);

        return app()->make(Files::class, ['request' => $request])->add();
    }

    /**
     * @param \League\Fractal\Resource\Item|\Symfony\Component\HttpFoundation\JsonResponse $answer
     */
    private function added($answer): File
    {
        static::assertInstanceOf(Item::class, $answer);
        $file = $answer->getData();
        static::assertInstanceOf(File::class, $file);

        return $file;
    }

    /**
     * @param array<string,mixed> $body
     *
     * @return \League\Fractal\Resource\Item|\Symfony\Component\HttpFoundation\JsonResponse
     */
    private function add(array $body)
    {
        $request = Request::create('/ccm/api/1.0/files', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], (string) json_encode($body));

        return app()->make(Files::class, ['request' => $request])->add();
    }

    /**
     * Hand the controller an incoming directory that lists its files and refuses to delete them, which is
     * what a directory nobody can write to does.
     */
    private function refuseToDeleteFromIncoming(): void
    {
        $incoming = app(Incoming::class);
        $filesystem = $this->createMock(FilesystemInterface::class);
        $filesystem->method('listContents')->willReturnCallback(static function ($directory = '', $recursive = false) use ($incoming) {
            return $incoming->getIncomingFilesystem()->listContents($directory, $recursive);
        });
        $filesystem->method('delete')->willReturn(false);
        $this->giveTheIncomingFilesystem($incoming, $filesystem);
    }

    /**
     * Take the file away between the import and the deletion, as another request would.
     */
    private function deleteFromIncomingBehindItsBack(): void
    {
        $incoming = app(Incoming::class);
        $realFilesystem = $incoming->getIncomingFilesystem();
        $filesystem = $this->createMock(FilesystemInterface::class);
        $filesystem->method('listContents')->willReturnCallback(static function ($directory = '', $recursive = false) use ($realFilesystem) {
            return $realFilesystem->listContents($directory, $recursive);
        });
        $filesystem->method('delete')->willReturnCallback(static function ($path) {
            throw new FileNotFoundException($path);
        });
        $this->giveTheIncomingFilesystem($incoming, $filesystem);
    }

    /**
     * @param \League\Flysystem\FilesystemInterface $filesystem
     */
    private function giveTheIncomingFilesystem(Incoming $incoming, $filesystem): void
    {
        $storageLocation = $incoming->getIncomingStorageLocation();
        $incomingPath = $incoming->getIncomingPath();
        $stub = $this->createMock(Incoming::class);
        $stub->method('getIncomingFilesystem')->willReturn($filesystem);
        $stub->method('getIncomingPath')->willReturn($incomingPath);
        // the importer reads the file through the storage location, which stays the real one
        $stub->method('getIncomingStorageLocation')->willReturn($storageLocation);
        app()->instance(Incoming::class, $stub);
    }

    private function putInIncoming(string $filename, string $contents): void
    {
        $incoming = app(Incoming::class);
        $incoming->getIncomingFilesystem()->put($incoming->getIncomingPath() . '/' . $filename, $contents);
    }

    /**
     * @return string[]
     */
    private function incomingFilenames(): array
    {
        $incoming = app(Incoming::class);
        $filenames = [];
        foreach ($incoming->getIncomingFilesystem()->listContents($incoming->getIncomingPath()) as $item) {
            if (($item['type'] ?? '') !== 'dir') {
                $filenames[] = $item['basename'];
            }
        }
        sort($filenames);

        return $filenames;
    }
}
