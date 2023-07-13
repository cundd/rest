<?php

declare(strict_types=1);

namespace Cundd\Rest\Tests\Functional\Integration;

use InvalidArgumentException;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use function array_merge;
use function file_exists;
use function realpath;
use function sprintf;
use function strlen;
use function substr;

trait FrontendSiteSetupTrait
{
    /**
     * Default Site Configuration
     * @var array
     */
    protected array $siteLanguageConfiguration = [
        1 => [
            'title'        => 'Dansk',
            'enabled'      => true,
            'languageId'   => 1,
            'base'         => '/dk/',
            'locale'       => 'da_DK.UTF-8',
            'flag'         => 'dk',
            'fallbackType' => 'fallback',
            'fallbacks'    => '0',
        ],
        2 => [
            'title'        => 'Deutsch',
            'enabled'      => true,
            'languageId'   => 2,
            'base'         => '/de/',
            'locale'       => 'de_DE.UTF-8',
            'flag'         => 'de',
            'fallbackType' => 'fallback',
            'fallbacks'    => '1,0',
        ],
    ];

    /**
     * Create a simple site config for the tests that
     * call a frontend page.
     */
    protected function setUpFrontendSite(int $pageId, array $additionalLanguages = []): void
    {
        $languages = [
            0 => [
                'title'           => 'English',
                'enabled'         => true,
                'languageId'      => 0,
                'base'            => '/',
                'locale'          => 'en_US.UTF-8',
                'navigationTitle' => '',
                'flag'            => 'us',
            ],
        ];
        $languages = array_merge($languages, $additionalLanguages);
        $configuration = [
            'rootPageId'    => $pageId,
            'base'          => '/',
            'languages'     => $languages,
            'errorHandling' => [],
            'routes'        => [],
        ];
        GeneralUtility::mkdir_deep($this->instancePath . '/typo3conf/sites/testing/');
        $yamlFileContents = Yaml::dump($configuration, 99, 2);
        $fileName = $this->instancePath . '/typo3conf/sites/testing/config.yaml';
        GeneralUtility::writeFile($fileName, $yamlFileContents);
        // Ensure that no other site configuration was cached before
        $cache = GeneralUtility::makeInstance(CacheManager::class)->getCache('core');
        if ($cache->has('sites-configuration')) {
            $cache->remove('sites-configuration');
        }
    }

    protected function prepareFrontendTypoScriptPath(string $inputPath): bool|string
    {
        if (!file_exists($inputPath)) {
            throw new InvalidArgumentException(sprintf('Could not find TypoScript file at "%s"', $inputPath));
        }
        $path = realpath($inputPath);
        if (false === $path) {
            throw new InvalidArgumentException(
                sprintf('Could not get realpath of TypoScript file at "%s"', $inputPath)
            );
        }

        $extensionBasePath = realpath(__DIR__ . '/../../../');
        if (str_starts_with($path, $extensionBasePath)) {
            return 'EXT:rest/' . substr($path, strlen($extensionBasePath) + 1);
        } else {
            return $path;
        }
    }
}
