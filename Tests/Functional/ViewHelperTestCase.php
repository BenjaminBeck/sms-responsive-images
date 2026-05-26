<?php
declare(strict_types=1);

namespace Sitegeist\ResponsiveImages\Tests\Functional;

use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariant;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Core\View\ViewInterface;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

abstract class ViewHelperTestCase extends FunctionalTestCase
{
    private const PUBLIC_IMAGE_PATTERN = '(?:typo3conf/ext/sms_responsive_images/Resources/Public|_assets/[a-f0-9]+)/Tests/Functional/Fixtures/ImageViewHelperTest\.png';

    protected array $testExtensionsToLoad = [
        'typo3conf/ext/sms_responsive_images'
    ];

    protected function createViewFromTemplateSource(string $templateSource): ViewInterface
    {
        $templateDirectory = $this->instancePath . '/typo3temp/var/tests/templates';
        if (!is_dir($templateDirectory)) {
            mkdir($templateDirectory, 0777, true);
        }
        $templatePathAndFilename = $templateDirectory . '/' . sha1($templateSource) . '.html';
        file_put_contents($templatePathAndFilename, $templateSource);

        return $this->get(ViewFactoryInterface::class)->create(
            new ViewFactoryData(templatePathAndFilename: $templatePathAndFilename)
        );
    }

    protected static function expandExpectedPattern(string $expected): string
    {
        return str_replace('###PUBLIC_IMAGE###', self::PUBLIC_IMAGE_PATTERN, $expected);
    }

    protected static function quoteExpectedTagPattern(string $expected): string
    {
        return '@^' . str_replace(
            '\#\#\#PUBLIC_IMAGE\#\#\#',
            self::PUBLIC_IMAGE_PATTERN,
            preg_quote($expected, '@')
        ) . '$@';
    }

    protected function createFileObjects(): array
    {
        $variables = [];
        $resourceFactory = $this->get(ResourceFactory::class);

        // Create file record from existing test file
        $variables['file'] = $resourceFactory->retrieveFileOrFolderObject(
            'EXT:sms_responsive_images/Resources/Public/Tests/Functional/Fixtures/ImageViewHelperTest.png'
        );

        // Create file reference with cropping information
        // Based on 400x300 dimensions
        $cropVariantCollection = new CropVariantCollection([
            new CropVariant('default', 'Default', Area::createEmpty()),
            new CropVariant('square', 'Square', new Area(0.125, 0, 0.75, 1)),
            new CropVariant('wide', 'Wide', new Area(0, 1 / 6, 1, 2 / 3)),
        ]);
        $variables['fileReference'] = $resourceFactory->createFileReferenceObject([
            'crop' => (string) $cropVariantCollection,
            'uid_local' => $variables['file']->getUid(),
            'alternative' => '',
            'title' => ''
        ]);

        return $variables;
    }
}
