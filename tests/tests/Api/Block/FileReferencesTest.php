<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Block;

use Concrete\TestHelpers\Block\BlockApiTestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Tests how the API tells a file apart: by its UUID, which means something on another site too.
 *
 * @see \Concrete\Core\Api\FileIdentifier
 */
class FileReferencesTest extends BlockApiTestCase
{
    public function testAFileTravelsByItsUuid(): void
    {
        $file = $this->getFile();
        $block = $this->addBlock('image', ['fID' => $file->getFileID(), 'sizingOption' => 'full_size']);

        $value = $this->getApiValue($block);

        static::assertSame($file->getFileUUID(), $value['fID']);
        static::assertNotSame((string) $file->getFileID(), $value['fID']);
    }

    public function testAFileComesBackFromItsUuid(): void
    {
        $file = $this->getFile();
        $block = $this->addBlock('image', ['fID' => 0, 'sizingOption' => 'full_size']);

        $this->updateBlock($block, ['fID' => $file->getFileUUID()]);

        static::assertSame(
            $file->getFileID(),
            (int) $this->getBlock($block->getBlockCollectionObject())->getController()->getBlockControllerData()->fID
        );
    }

    /**
     * The UUID is there to keep the IDs of the site to itself: a file that has one answers to that
     * one only.
     */
    public function testAFileWithAUuidDoesntComeBackFromItsId(): void
    {
        $file = $this->getFile();
        $block = $this->addBlock('image', ['fID' => 0, 'sizingOption' => 'full_size']);

        $this->updateBlock($block, ['fID' => $file->getFileID()]);

        static::assertSame(0, $this->getApiValue($this->getBlock($block->getBlockCollectionObject()))['fID']);
    }

    public function testNoFileStaysNoFile(): void
    {
        $block = $this->addBlock('image', ['fID' => 0, 'sizingOption' => 'full_size']);

        static::assertSame(0, $this->getApiValue($block)['fID']);
    }

    public function testTheSchemaSaysThatAFileMayBeAString(): void
    {
        $block = $this->addBlock('image', ['fID' => 0, 'sizingOption' => 'full_size']);

        $described = $this->getHandler($block)->getApiValueSchema()['properties']['fID'];

        static::assertSame(['string', 'integer', 'null'], $described['type']);
        static::assertSame('file', $described['x-concrete-reference']);
    }

    public function testTheSchemaTellsHowAFileIsNamed(): void
    {
        $block = $this->addBlock('image', ['fID' => 0, 'sizingOption' => 'full_size']);

        $described = $this->getHandler($block)->getApiValueSchema()['properties']['fID'];

        static::assertStringContainsString('UUID', $described['description']);
    }

    public function testTheSchemaTellsHowARichTextRefersToTheSite(): void
    {
        $block = $this->addBlock('content', ['content' => 'Hello']);

        $description = $this->getHandler($block)->getApiValueSchema()['properties']['content']['description'];

        static::assertStringContainsString('{CCM:CID_<page ID>}', $description);
        static::assertStringContainsString('{CCM:FID_DL_<file>}', $description);
        static::assertStringContainsString('<concrete-picture', $description);
        static::assertStringContainsString('{CCM:BASE_URL}', $description);
    }

    public function testTheFilesOfARichTextTravelByTheirUuidToo(): void
    {
        $file = $this->getFile();
        $block = $this->addBlock('content', [
            'content' => 'A <a href="{CCM:FID_DL_' . $file->getFileID() . '}">link</a>, the <a href="{CCM:FID_' . $file->getFileID() . '}">file</a> and a <concrete-picture fID="' . $file->getFileID() . '" alt="" />',
        ]);

        $content = $this->getApiValue($block)['content'];

        static::assertStringContainsString('{CCM:FID_DL_' . $file->getFileUUID() . '}', $content);
        static::assertStringContainsString('{CCM:FID_' . $file->getFileUUID() . '}', $content);
        // the editor stores the attribute as it pleases, lower-cased at the time of writing
        static::assertStringContainsStringIgnoringCase('fID="' . $file->getFileUUID() . '"', $content);
    }
}
