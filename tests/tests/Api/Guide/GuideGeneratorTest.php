<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Guide;

use Concrete\Core\Api\Events\GenerateApiGuideEvent;
use Concrete\Core\Api\Guide\GuideGenerator;
use Concrete\Core\Api\Guide\PlaceholderFactory;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Events\EventDispatcher;
use Concrete\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @covers \Concrete\Core\Api\Guide\GuideGenerator
 */
class GuideGeneratorTest extends TestCase
{
    public function testThePlaceholdersAreReplacedWithTheValuesOfTheInstallation(): void
    {
        $generator = $this->createGenerator('# %siteName%', ['siteName' => 'Example']);

        static::assertSame('# Example', $generator->getGuide());
    }

    public function testAPlaceholderWithNoValueIsLeftWhereItIs(): void
    {
        $generator = $this->createGenerator('# %whatever%', ['siteName' => 'Example']);

        static::assertSame('# %whatever%', $generator->getGuide());
    }

    public function testListenersCanChangeTheGuideAndThePlaceholders(): void
    {
        $symfonyDispatcher = new SymfonyEventDispatcher();
        $symfonyDispatcher->addListener(GuideGenerator::EVENTNAME_GENERATE, static function (GenerateApiGuideEvent $event): void {
            $event
                ->setMarkdown($event->getMarkdown() . "\n\n## %ours%")
                ->setPlaceholders(['siteName' => 'Theirs', 'ours' => 'Ours'])
            ;
        });
        $generator = $this->createGenerator('# %siteName%', ['siteName' => 'Example'], $symfonyDispatcher);

        static::assertSame("# Theirs\n\n## Ours", $generator->getGuide());
    }

    public function testTheGuideCantBeBuiltWithoutItsFile(): void
    {
        $generator = $this->createGenerator(null, []);

        $this->expectException(UserMessageException::class);

        $generator->getGuide();
    }

    /**
     * @param string|null $markdown the contents of the markdown file, NULL if there's no such file
     * @param array<string,string> $placeholders
     */
    private function createGenerator(?string $markdown, array $placeholders, ?SymfonyEventDispatcher $symfonyDispatcher = null): GuideGenerator
    {
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->method('exists')->willReturn($markdown !== null);
        $filesystem->method('get')->willReturn((string) $markdown);
        $placeholderFactory = $this->createMock(PlaceholderFactory::class);
        $placeholderFactory->method('getPlaceholders')->willReturn($placeholders);

        return new GuideGenerator(
            new EventDispatcher($symfonyDispatcher ?? new SymfonyEventDispatcher()),
            $filesystem,
            $placeholderFactory
        );
    }
}
