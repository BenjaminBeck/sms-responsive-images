<?php

namespace Sitegeist\ResponsiveImages\Tests\Unit\Utility\ResponsiveImagesUtility;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;
use TYPO3\CMS\Core\Imaging\ImageManipulation\Area;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariant;

class PictureTagTest extends AbstractResponsiveImagesUtilityTestCase
{
    public static function createPictureTagProvider()
    {
        $cropVariantCollection = new CropVariantCollection([
            new CropVariant('desktop', 'Desktop', Area::createEmpty()),
            new CropVariant('mobile', 'Mobile', Area::createEmpty())
        ]);

        return [
            // Test two breakpoints with media queries with standard output
            'usingTwoBreakpointsWithMediaRequestingStandardOutput' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                [
                    [
                        'cropVariant' => 'desktop',
                        'srcset' => [500, 1000],
                        'media' => 'media desktop',
                        'sizes' => 'sizes desktop'
                    ],
                    [
                        'cropVariant' => 'mobile',
                        'srcset' => [400, 800],
                        'media' => 'media mobile',
                        'sizes' => 'sizes mobile'
                    ]
                ],
                $cropVariantCollection,
                null,
                false,
                0,
                false,
                'picture',
                [
                    '<source srcset="/image-500.jpg 500w, /image-1000.jpg 1000w" media="media desktop" sizes="sizes desktop" width="1000" height="1000" />',
                    '<source srcset="/image-400.jpg 400w, /image-800.jpg 800w" media="media mobile" sizes="sizes mobile" width="800" height="800" />',
                    '<img src="/image-1000.jpg" width="1000" height="1000" alt="" />'
                ]
            ],
            // Test two breakpoints, last one without media query, with standard output
            'usingTwoBreakpointsLastWithoutMediaRequestingStandardOutput' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                [
                    [
                        'cropVariant' => 'desktop',
                        'srcset' => [500, 1000],
                        'media' => 'media desktop',
                        'sizes' => 'sizes desktop'
                    ],
                    ['cropVariant' => 'mobile', 'srcset' => [400, 800], 'sizes' => 'sizes mobile']
                ],
                $cropVariantCollection,
                null,
                false,
                0,
                false,
                'picture',
                [
                    '<source srcset="/image-500.jpg 500w, /image-1000.jpg 1000w" media="media desktop" sizes="sizes desktop" width="1000" height="1000" />',
                    '<source srcset="/image-400.jpg 400w, /image-800.jpg 800w" sizes="sizes mobile" width="800" height="800" />',
                    '<img src="/image-1000.jpg" width="1000" height="1000" alt="" />'
                ]
            ],
            // Test focus area
            'usingFocusArea' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                [],
                $cropVariantCollection,
                new Area(0.4, 0.4, 0.6, 0.6),
                false,
                0,
                false,
                'picture',
                [
                    '<img src="/image-1000.jpg" width="1000" height="1000" data-focus-area="'
                        . htmlspecialchars(json_encode(['x' => 400, 'y' => 400, 'width' => 600, 'height' => 600]))
                        . '" alt="" />'
                ]
            ],
            // Test image metadata attributes
            'usingMetadata' => [
                ['width' => 2000, 'height' => 2000, 'alternative' => 'image alt', 'title' => 'image title', 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                [],
                $cropVariantCollection,
                null,
                false,
                0,
                false,
                'picture',
                [
                    '<img src="/image-1000.jpg" width="1000" height="1000" alt="image alt" title="image title" />'
                ]
            ],
            // Test lazyload markup with standard output
            'usingLazyloadRequestingStandardOutput' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                [
                    [
                        'cropVariant' => 'desktop',
                        'srcset' => [500, 1000],
                        'media' => 'media desktop',
                        'sizes' => 'sizes desktop'
                    ],
                    ['cropVariant' => 'mobile', 'srcset' => [400, 800], 'sizes' => 'sizes mobile']
                ],
                $cropVariantCollection,
                null,
                true,
                0,
                false,
                'picture',
                [
                    '<source data-srcset="/image-500.jpg 500w, /image-1000.jpg 1000w" media="media desktop" sizes="sizes desktop" width="1000" height="1000" />',
                    '<source data-srcset="/image-400.jpg 400w, /image-800.jpg 800w" sizes="sizes mobile" width="800" height="800" />',
                    '<img data-src="/image-1000.jpg" class="lazyload" width="1000" height="1000" alt="" />'
                ]
            ],
            // Test lazyload markup with placeholder
            'usingLazyloadWithPlaceholder' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                [
                    [
                        'cropVariant' => 'desktop',
                        'srcset' => [500, 1000],
                        'media' => 'media desktop',
                        'sizes' => 'sizes desktop'
                    ],
                    ['cropVariant' => 'mobile', 'srcset' => [400, 800], 'sizes' => 'sizes mobile']
                ],
                $cropVariantCollection,
                null,
                true,
                20,
                false,
                'picture',
                [
                    '<source data-srcset="/image-500.jpg 500w, /image-1000.jpg 1000w" media="media desktop" sizes="sizes desktop" width="1000" height="1000" />',
                    '<source data-srcset="/image-400.jpg 400w, /image-800.jpg 800w" sizes="sizes mobile" width="800" height="800" />',
                    '<img data-src="/image-1000.jpg" class="lazyload" src="/image-20.jpg" width="1000" height="1000" alt="" />'
                ]
            ],
            // Test lazyload markup with inline placeholder
            'usingLazyloadWithInlinePlaceholder' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg', 'mimeType' => 'image/jpeg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                [
                    [
                        'cropVariant' => 'desktop',
                        'srcset' => [500, 1000],
                        'media' => 'media desktop',
                        'sizes' => 'sizes desktop'
                    ],
                    ['cropVariant' => 'mobile', 'srcset' => [400, 800], 'sizes' => 'sizes mobile']
                ],
                $cropVariantCollection,
                null,
                true,
                20,
                true,
                'picture',
                [
                    '<source data-srcset="/image-500.jpg 500w, /image-1000.jpg 1000w" media="media desktop" sizes="sizes desktop" width="1000" height="1000" />',
                    '<source data-srcset="/image-400.jpg 400w, /image-800.jpg 800w" sizes="sizes mobile" width="800" height="800" />',
                    '<img data-src="/image-1000.jpg" class="lazyload" src="data:image/jpeg;base64,ZGFzLWlzdC1kZXItZGF0ZWlpbmhhbHQ=" width="1000" height="1000" alt="" />'
                ]
            ],
        ];
    }

    #[Test]
    #[DataProvider('createPictureTagProvider')]
    public function createPictureTag(
        $originalImage,
        $fallbackImage,
        $breakpoints,
        $cropVariantCollection,
        $focusArea,
        $lazyload,
        $placeholderSize,
        $placeholderInline,
        $tagName,
        $tagContent
    ) {
        $originalImage = $this->mockFileObject($originalImage);
        $fallbackImage = $this->mockFileObject($fallbackImage);

        $tag = $this->utility->createPictureTag(
            $originalImage,
            $fallbackImage,
            $breakpoints,
            $cropVariantCollection,
            $focusArea,
            null,
            null,
            false,
            $lazyload,
            'svg',
            $placeholderSize,
            $placeholderInline
        );
        $this->assertEquals($tagName, $tag->getTagName());
        $this->assertEquals(implode('', $tagContent), $tag->getContent());
    }

    public static function createPictureTagWithCustomTagProvider()
    {
        $pictureTag = new TagBuilder('picture-custom');
        $pictureTag->addAttribute('test', 'test attribute');

        return [
            // Test if tag attributes persist
            'usingCustomTag' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                new CropVariantCollection([]),
                $pictureTag,
                'picture-custom',
                'test attribute'
            ]
        ];
    }

    #[Test]
    #[DataProvider('createPictureTagWithCustomTagProvider')]
    public function createPictureTagWithCustomTag(
        $originalImage,
        $fallbackImage,
        $cropVariantCollection,
        $pictureTag,
        $tagName,
        $testAttribute
    ) {
        $originalImage = $this->mockFileObject($originalImage);
        $fallbackImage = $this->mockFileObject($fallbackImage);

        $tag = $this->utility->createPictureTag(
            $originalImage,
            $fallbackImage,
            [],
            $cropVariantCollection,
            null,
            $pictureTag
        );
        $this->assertEquals($tagName, $tag->getTagName());
        $this->assertEquals($testAttribute, $tag->getAttribute('test'));
    }

    public static function createPictureTagWithCustomFallbackTagProvider()
    {
        $fallbackTag = new TagBuilder('img');
        $fallbackTag->addAttribute('alt', 'fixed alt');
        $fallbackTag->addAttribute('title', 'fixed title');
        $fallbackTag->addAttribute('longdesc', 'fixed longdesc');
        $fallbackTag->addAttribute('class', 'myClass');

        return [
            // Test if fallback tag attributes persist
            'usingCustomFallbackTag' => [
                ['width' => 2000, 'height' => 2000, 'alternative' => 'image alt', 'title' => 'image title', 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                new CropVariantCollection([]),
                clone $fallbackTag,
                false,
                ['<img alt="fixed alt" title="fixed title" longdesc="fixed longdesc" class="myClass" src="/image-1000.jpg" width="1000" height="1000" />']
            ],
            // Test if fallback tag works with lazyloading
            'usingCustomFallbackTagWithLazyload' => [
                ['width' => 2000, 'height' => 2000, 'alternative' => 'image alt', 'title' => 'image title', 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                new CropVariantCollection([]),
                clone $fallbackTag,
                true,
                ['<img alt="fixed alt" title="fixed title" longdesc="fixed longdesc" class="myClass lazyload" data-src="/image-1000.jpg" width="1000" height="1000" />']
            ]
        ];
    }

    #[Test]
    #[DataProvider('createPictureTagWithCustomFallbackTagProvider')]
    public function createPictureTagWithCustomFallbackTag(
        $originalImage,
        $fallbackImage,
        $cropVariantCollection,
        $fallbackTag,
        $lazyload,
        $tagContent
    ) {
        $originalImage = $this->mockFileObject($originalImage);
        $fallbackImage = $this->mockFileObject($fallbackImage);

        $tag = $this->utility->createPictureTag(
            $originalImage,
            $fallbackImage,
            [],
            $cropVariantCollection,
            null,
            null,
            $fallbackTag,
            false,
            $lazyload
        );
        $this->assertEquals(implode('', $tagContent), $tag->getContent());
    }

    public static function createPictureTagFromSvgProvider()
    {
        return [
            // Test if fallback tag attributes persist
            'withSvgImage' => [
                ['width' => 2000, 'height' => 2000, 'alternative' => 'image alt', 'title' => 'image title', 'extension' => 'svg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'svg'],
                'img',
                '/image-2000.svg',
                null,
                1000,
                1000
            ]
        ];
    }

    #[Test]
    #[DataProvider('createPictureTagFromSvgProvider')]
    public function createPictureTagFromSvg($originalImage, $fallbackImage, $tagName, $srcAttribute, $srcsetAttribute, $heightAttribute, $widthAttribute)
    {
        $originalImage = $this->mockFileObject($originalImage);
        $fallbackImage = $this->mockFileObject($fallbackImage);

        $tag = $this->utility->createPictureTag(
            $originalImage,
            $fallbackImage,
            [],
            new CropVariantCollection([])
        );
        $this->assertEquals($tagName, $tag->getTagName());
        $this->assertEquals($srcAttribute, $tag->getAttribute('src'));
        $this->assertEquals($srcsetAttribute, $tag->getAttribute('srcset'));
        $this->assertEquals($widthAttribute, $tag->getAttribute('width'));
        $this->assertEquals($heightAttribute, $tag->getAttribute('height'));
    }

    public static function createPictureTagWithCustomFileExtensionProvider()
    {
        $cropVariantCollection = new CropVariantCollection([
            new CropVariant('desktop', 'Desktop', Area::createEmpty()),
            new CropVariant('mobile', 'Mobile', Area::createEmpty())
        ]);

        return [
            // Test two breakpoints with media queries with standard output
            'usingTwoBreakpoints' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                [
                    [
                        'cropVariant' => 'desktop',
                        'srcset' => [500, 1000],
                        'media' => 'media desktop',
                        'sizes' => 'sizes desktop'
                    ],
                    [
                        'cropVariant' => 'mobile',
                        'srcset' => [400, 800],
                        'media' => 'media mobile',
                        'sizes' => 'sizes mobile'
                    ]
                ],
                $cropVariantCollection,
                null,
                false,
                0,
                false,
                'picture',
                [
                    '<source srcset="/image-500.webp 500w, /image-1000.webp 1000w" media="media desktop" sizes="sizes desktop" width="1000" height="1000" />',
                    '<source srcset="/image-400.webp 400w, /image-800.webp 800w" media="media mobile" sizes="sizes mobile" width="800" height="800" />',
                    '<img src="/image-1000.jpg" width="1000" height="1000" alt="" />'
                ]
            ],
            // Test lazyload markup with standard output
            'usingLazyload' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                [
                    [
                        'cropVariant' => 'desktop',
                        'srcset' => [500, 1000],
                        'media' => 'media desktop',
                        'sizes' => 'sizes desktop'
                    ],
                    ['cropVariant' => 'mobile', 'srcset' => [400, 800], 'sizes' => 'sizes mobile']
                ],
                $cropVariantCollection,
                null,
                true,
                0,
                false,
                'picture',
                [
                    '<source data-srcset="/image-500.webp 500w, /image-1000.webp 1000w" media="media desktop" sizes="sizes desktop" width="1000" height="1000" />',
                    '<source data-srcset="/image-400.webp 400w, /image-800.webp 800w" sizes="sizes mobile" width="800" height="800" />',
                    '<img data-src="/image-1000.jpg" class="lazyload" width="1000" height="1000" alt="" />'
                ]
            ],
                        // Test lazyload markup with placeholder
            'usingLazyloadWithPlaceholder' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                [
                    [
                        'cropVariant' => 'desktop',
                        'srcset' => [500, 1000],
                        'media' => 'media desktop',
                        'sizes' => 'sizes desktop'
                    ],
                    ['cropVariant' => 'mobile', 'srcset' => [400, 800], 'sizes' => 'sizes mobile']
                ],
                $cropVariantCollection,
                null,
                true,
                20,
                false,
                'picture',
                [
                    '<source data-srcset="/image-500.webp 500w, /image-1000.webp 1000w" media="media desktop" sizes="sizes desktop" width="1000" height="1000" />',
                    '<source data-srcset="/image-400.webp 400w, /image-800.webp 800w" sizes="sizes mobile" width="800" height="800" />',
                    '<img data-src="/image-1000.jpg" class="lazyload" src="/image-20.webp" width="1000" height="1000" alt="" />'
                ]
            ],
            // Test lazyload markup with inline placeholder
            'usingLazyloadWithInlinePlaceholder' => [
                ['width' => 2000, 'height' => 2000, 'extension' => 'jpg', 'mimeType' => 'image/jpeg'],
                ['width' => 1000, 'height' => 1000, 'extension' => 'jpg'],
                [
                    [
                        'cropVariant' => 'desktop',
                        'srcset' => [500, 1000],
                        'media' => 'media desktop',
                        'sizes' => 'sizes desktop'
                    ],
                    ['cropVariant' => 'mobile', 'srcset' => [400, 800], 'sizes' => 'sizes mobile']
                ],
                $cropVariantCollection,
                null,
                true,
                20,
                true,
                'picture',
                [
                    '<source data-srcset="/image-500.webp 500w, /image-1000.webp 1000w" media="media desktop" sizes="sizes desktop" width="1000" height="1000" />',
                    '<source data-srcset="/image-400.webp 400w, /image-800.webp 800w" sizes="sizes mobile" width="800" height="800" />',
                    '<img data-src="/image-1000.jpg" class="lazyload" src="data:image/webp;base64,ZGFzLWlzdC1kZXItZGF0ZWlpbmhhbHQ=" width="1000" height="1000" alt="" />'
                ]
            ],
        ];
    }

    #[Test]
    #[DataProvider('createPictureTagWithCustomFileExtensionProvider')]
    public function createPictureTagWithCustomFileExtension(
        $originalImage,
        $fallbackImage,
        $breakpoints,
        $cropVariantCollection,
        $focusArea,
        $lazyload,
        $placeholderSize,
        $placeholderInline,
        $tagName,
        $tagContent
    ) {
        $originalImage = $this->mockFileObject($originalImage);
        $fallbackImage = $this->mockFileObject($fallbackImage);

        $tag = $this->utility->createPictureTag(
            $originalImage,
            $fallbackImage,
            $breakpoints,
            $cropVariantCollection,
            $focusArea,
            null,
            null,
            false,
            $lazyload,
            'svg',
            $placeholderSize,
            $placeholderInline,
            'webp'
        );
        $this->assertEquals($tagName, $tag->getTagName());
        $this->assertEquals(implode('', $tagContent), $tag->getContent());
    }

    #[Test]
    public function createPictureTagWithJpegFallbackSourceAndLqipBackground(): void
    {
        $cropVariantCollection = new CropVariantCollection([
            new CropVariant('desktop', 'Desktop', Area::createEmpty()),
            new CropVariant('mobile', 'Mobile', Area::createEmpty())
        ]);
        $originalImage = $this->mockFileObject([
            'width' => 2000,
            'height' => 1200,
            'extension' => 'jpg',
            'mimeType' => 'image/jpeg',
        ]);
        $fallbackImage = $this->mockFileObject([
            'width' => 1000,
            'height' => 600,
            'extension' => 'webp',
            'mimeType' => 'image/webp',
        ]);

        $tag = $this->utility->createPictureTag(
            $originalImage,
            $fallbackImage,
            [
                [
                    'cropVariant' => 'desktop',
                    'srcset' => [500, 1000],
                    'media' => '(min-width: 768px)',
                    'sizes' => 'auto',
                ],
                [
                    'cropVariant' => 'mobile',
                    'srcset' => [235, 470],
                    'sizes' => 'auto, 50vw',
                ],
            ],
            $cropVariantCollection,
            null,
            null,
            null,
            false,
            false,
            'svg, gif',
            64,
            true,
            'webp',
            'desktop',
            true,
            50,
            true,
            2
        );

        $this->assertSame('picture', $tag->getTagName());
        $this->assertSame('has-lqip', $tag->getAttribute('class'));
        $this->assertSame('--lqip: url(&quot;data:image/webp;base64,ZGFzLWlzdC1kZXItZGF0ZWlpbmhhbHQ=&quot;);', $tag->getAttribute('style'));
        $this->assertSame(
            '<source srcset="/image-500-q50.webp 500w, /image-1000-q50.webp 1000w" media="(min-width: 768px)" sizes="auto" width="1000" height="600" type="image/webp" />'
            . '<source srcset="/image-235-q50.webp 235w, /image-470-q50.webp 470w" sizes="auto, 50vw" width="470" height="282" type="image/webp" />'
            . '<source srcset="/image-1000-q50.jpg" type="image/jpeg" width="1000" height="600" />'
                        . '<img src="/image-1000-q50.jpg" width="1000" height="600" alt="" data-sizes="auto" />',
            $tag->getContent()
        );
    }

    #[Test]
    public function createPictureTagDoesNotAddWebpSourcesByDefault(): void
    {
        $cropVariantCollection = new CropVariantCollection([
            new CropVariant('desktop', 'Desktop', Area::createEmpty()),
            new CropVariant('mobile', 'Mobile', Area::createEmpty())
        ]);
        $originalImage = $this->mockFileObject([
            'width' => 2000,
            'height' => 1200,
            'extension' => 'jpg',
            'mimeType' => 'image/jpeg',
        ]);
        $fallbackImage = $this->mockFileObject([
            'width' => 1000,
            'height' => 600,
            'extension' => 'jpg',
            'mimeType' => 'image/jpeg',
        ]);

        $tag = $this->utility->createPictureTag(
            $originalImage,
            $fallbackImage,
            [
                [
                    'cropVariant' => 'desktop',
                    'srcset' => [500, 1000],
                    'media' => '(min-width: 768px)',
                    'sizes' => 'auto',
                ],
                [
                    'cropVariant' => 'mobile',
                    'srcset' => [235, 470],
                    'sizes' => 'auto, 50vw',
                ],
            ],
            $cropVariantCollection,
            null,
            null,
            null,
            false,
            false,
            'svg, gif',
            0,
            false,
            null,
            'desktop',
            false,
            50
        );

        $this->assertSame('picture', $tag->getTagName());
        $this->assertSame(
            '<source srcset="/image-500-q50.jpg 500w, /image-1000-q50.jpg 1000w" media="(min-width: 768px)" sizes="auto" width="1000" height="600" type="image/jpeg" />'
            . '<source srcset="/image-235-q50.jpg 235w, /image-470-q50.jpg 470w" sizes="auto, 50vw" width="470" height="282" type="image/jpeg" />'
            . '<img src="/image-1000.jpg" width="1000" height="600" alt="" />',
            $tag->getContent()
        );
    }

    #[Test]
    public function createPictureTagAddsWebpSourcesWhenEnabled(): void
    {
        $packageManagerProperty = new \ReflectionProperty(ExtensionManagementUtility::class, 'packageManager');
        $packageManagerProperty->setAccessible(true);
        $originalPackageManager = $packageManagerProperty->getValue();

        $packageManager = $this->createStub(PackageManager::class);
        $packageManager
            ->method('isPackageActive')
            ->willReturnCallback(static fn($packageKey) => $packageKey === 'webp');

        ExtensionManagementUtility::setPackageManager($packageManager);

        try {
            $cropVariantCollection = new CropVariantCollection([
                new CropVariant('desktop', 'Desktop', Area::createEmpty()),
                new CropVariant('mobile', 'Mobile', Area::createEmpty())
            ]);
            $originalImage = $this->mockFileObject([
                'width' => 2000,
                'height' => 1200,
                'extension' => 'jpg',
                'mimeType' => 'image/jpeg',
            ]);
            $fallbackImage = $this->mockFileObject([
                'width' => 1000,
                'height' => 600,
                'extension' => 'jpg',
                'mimeType' => 'image/jpeg',
            ]);

            $tag = $this->utility->createPictureTag(
                $originalImage,
                $fallbackImage,
                [
                    [
                        'cropVariant' => 'desktop',
                        'srcset' => [500, 1000],
                        'media' => '(min-width: 768px)',
                        'sizes' => 'auto',
                    ],
                    [
                        'cropVariant' => 'mobile',
                        'srcset' => [235, 470],
                        'sizes' => 'auto, 50vw',
                    ],
                ],
                $cropVariantCollection,
                null,
                null,
                null,
                false,
                false,
                'svg, gif',
                0,
                false,
                null,
                'desktop',
                false,
                50,
                false,
                null,
                false,
                null,
                true
            );

            $this->assertSame('picture', $tag->getTagName());
            $this->assertSame(
                '<source srcset="/image-500-q50.jpg.webp 500w, /image-1000-q50.jpg.webp 1000w" media="(min-width: 768px)" sizes="auto" width="1000" height="600" type="image/webp" />'
                . '<source srcset="/image-500-q50.jpg 500w, /image-1000-q50.jpg 1000w" media="(min-width: 768px)" sizes="auto" width="1000" height="600" type="image/jpeg" />'
                . '<source srcset="/image-235-q50.jpg.webp 235w, /image-470-q50.jpg.webp 470w" sizes="auto, 50vw" width="470" height="282" type="image/webp" />'
                . '<source srcset="/image-235-q50.jpg 235w, /image-470-q50.jpg 470w" sizes="auto, 50vw" width="470" height="282" type="image/jpeg" />'
                . '<img src="/image-1000.jpg" width="1000" height="600" alt="" />',
                $tag->getContent()
            );
        } finally {
            ExtensionManagementUtility::setPackageManager($originalPackageManager);
        }
    }

    #[Test]
    public function createPictureTagWithAvifSources(): void
    {
        $cropVariantCollection = new CropVariantCollection([
            new CropVariant('desktop', 'Desktop', Area::createEmpty()),
            new CropVariant('mobile', 'Mobile', Area::createEmpty())
        ]);
        $originalImage = $this->mockFileObject([
            'width' => 2000,
            'height' => 1200,
            'extension' => 'jpg',
            'mimeType' => 'image/jpeg',
        ]);
        $fallbackImage = $this->mockFileObject([
            'width' => 1000,
            'height' => 600,
            'extension' => 'jpg',
            'mimeType' => 'image/jpeg',
        ]);

        $tag = $this->utility->createPictureTag(
            $originalImage,
            $fallbackImage,
            [
                [
                    'cropVariant' => 'desktop',
                    'srcset' => [500, 1000],
                    'media' => '(min-width: 768px)',
                    'sizes' => 'auto',
                ],
                [
                    'cropVariant' => 'mobile',
                    'srcset' => [235, 470],
                    'sizes' => 'auto, 50vw',
                ],
            ],
            $cropVariantCollection,
            null,
            null,
            null,
            false,
            false,
            'svg, gif',
            0,
            false,
            null,
            'desktop',
            false,
            null,
            false,
            null,
            true,
            38
        );

        $this->assertSame('picture', $tag->getTagName());
        $this->assertSame(
            '<source srcset="/image-500-q38.avif 500w, /image-1000-q38.avif 1000w" media="(min-width: 768px)" sizes="auto" width="1000" height="600" type="image/avif" />'
            . '<source srcset="/image-500.jpg 500w, /image-1000.jpg 1000w" media="(min-width: 768px)" sizes="auto" width="1000" height="600" />'
            . '<source srcset="/image-235-q38.avif 235w, /image-470-q38.avif 470w" sizes="auto, 50vw" width="470" height="282" type="image/avif" />'
            . '<source srcset="/image-235.jpg 235w, /image-470.jpg 470w" sizes="auto, 50vw" width="470" height="282" />'
            . '<img src="/image-1000.jpg" width="1000" height="600" alt="" />',
            $tag->getContent()
        );
    }

    #[Test]
    public function createPictureTagKeepsSvgSimpleWhenCustomExtensionIsRequested(): void
    {
        $originalImage = $this->mockFileObject([
            'width' => 2000,
            'height' => 2000,
            'alternative' => 'svg alt',
            'extension' => 'svg',
            'mimeType' => 'image/svg+xml',
        ]);
        $fallbackImage = $this->mockFileObject([
            'width' => 1000,
            'height' => 1000,
            'extension' => 'svg',
            'mimeType' => 'image/svg+xml',
        ]);

        $tag = $this->utility->createPictureTag(
            $originalImage,
            $fallbackImage,
            [['srcset' => [500, 1000]]],
            new CropVariantCollection([]),
            null,
            null,
            null,
            false,
            false,
            'svg, gif',
            0,
            false,
            'webp'
        );

        $this->assertSame('img', $tag->getTagName());
        $this->assertSame('/image-2000.svg', $tag->getAttribute('src'));
        $this->assertSame('picture', $tag->getAttribute('class'));
        $this->assertSame('svg alt', $tag->getAttribute('alt'));
    }
}
