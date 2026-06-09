<?php

namespace Sitegeist\ResponsiveImages\Tests\Unit\Utility\ResponsiveImagesUtility;

use Sitegeist\ResponsiveImages\Utility\ResponsiveImagesUtility;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Extbase\Service\ImageService;

abstract class AbstractResponsiveImagesUtilityTestCase extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    protected ResponsiveImagesUtility $utility;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetSingletonInstances = true;

        $this->utility = new ResponsiveImagesUtility(
            $this->mockImageService()
        );
    }

    protected function mockImageService()
    {
        $test = $this;

        $imageServiceMock = $this->createStub(ImageService::class);

        $imageServiceMock
            ->method('applyProcessingInstructions')
            ->willReturnCallback(function ($file, $instructions) use ($test) {
                // Simulate processor_allowUpscaling = false
                $instructions['width'] = isset($instructions['width'])
                    ? min($instructions['width'], $file->getProperty('width'))
                    : $file->getProperty('width');
                if (!isset($instructions['height']) && $file->getProperty('width') && $file->getProperty('height')) {
                    $instructions['height'] = (int)round(
                        $instructions['width'] * $file->getProperty('height') / $file->getProperty('width')
                    );
                }

                // Use file name and extension from original image
                $instructions['name'] = $file->getProperty('name');

                if (isset($instructions['fileExtension'])) {
                    $instructions['extension'] = $instructions['fileExtension'];
                    $instructions['mimeType'] = 'image/' . $instructions['fileExtension'];
                    unset($instructions['fileExtension']);
                } else {
                    $instructions['extension'] = $file->getProperty('extension');
                    $instructions['mimeType'] = $file->getProperty('mimeType');
                }

                if (isset($instructions['additionalParameters'])) {
                    $instructions['quality'] = trim(str_replace('-quality', '', $instructions['additionalParameters']));
                }

                return $test->mockFileObject($instructions, true);
            });

        $imageServiceMock
            ->method('getImageUri')
            ->willReturnCallback(function ($file, $absolute) {
                $qualitySuffix = $file->getProperty('quality') !== null ? '-q' . $file->getProperty('quality') : '';
                return (($absolute) ? 'http://domain.tld' : '') . '/' . $file->getProperty('name') . '-' . $file->getProperty('width')
                    . $qualitySuffix . '.' . $file->getProperty('extension');
            });

        return $imageServiceMock;
    }

    protected function mockFileObject($properties, $processed = false)
    {
        $defaultProperties = [
            'name' => 'image'
        ];
        $properties = array_replace($defaultProperties, $properties);

        if ($processed) {
            $fileMock = $this->createStub(ProcessedFile::class);

            $fileMock
                ->method('usesOriginalFile')
                ->willReturnCallback(function () use ($properties) {
                    return false;
                });
        } else {
            $fileMock = $this->createStub(FileReference::class);
        }

        $fileMock
            ->method('getProperty')
            ->willReturnCallback(function ($property) use ($properties) {
                return $properties[$property] ?? null;
            });
        $fileMock
            ->method('getMimeType')
            ->willReturnCallback(function () use ($properties) {
                return $properties['mimeType'];
            });
        $fileMock
            ->method('getContents')
            ->willReturnCallback(function () use ($properties) {
                return 'das-ist-der-dateiinhalt';
            });

        return $fileMock;
    }
}
