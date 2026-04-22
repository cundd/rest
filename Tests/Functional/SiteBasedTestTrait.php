<?php

namespace Cundd\Rest\Tests\Functional;

use Exception;
use LogicException;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Configuration\SiteConfiguration;
use TYPO3\CMS\Core\Configuration\SiteWriter;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\Internal\ArrayValueInstruction;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\Internal\InstructionInterface;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\Internal\TypoScriptInstruction;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;

/**
 * @see https://git.typo3.org/typo3/typo3/-/blob/main/typo3/sysext/core/Tests/Functional/SiteHandling/SiteBasedTestTrait.php
 */
trait SiteBasedTestTrait
{
    /**
     * @param array<string,mixed>       $site
     * @param list<array<string,mixed>> $languages
     * @param array<int,mixed>          $errorHandling
     * @param array<int,mixed>          $dependencies
     * @param array<string,mixed>       $csp
     */
    protected function writeSiteConfiguration(
        string $identifier,
        array $site = [],
        array $languages = [],
        array $errorHandling = [],
        array $dependencies = [],
        ?array $csp = null,
    ): void {
        $configuration = $site;
        if (!empty($languages)) {
            $configuration['languages'] = $languages;
        }
        if (!empty($errorHandling)) {
            $configuration['errorHandling'] = $errorHandling;
        }
        if (!empty($dependencies)) {
            $configuration['dependencies'] = $dependencies;
        }
        $siteWriter = $this->get(SiteWriter::class);
        try {
            // ensure no previous site configuration influences the test
            GeneralUtility::rmdir($this->instancePath . '/typo3conf/sites/' . $identifier, true);
            $siteWriter->write($identifier, $configuration);
            if (null !== $csp) {
                GeneralUtility::writeFile(
                    $this->instancePath . '/typo3conf/sites/' . $identifier . '/csp.yaml',
                    Yaml::dump($csp, 99, 2, Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE | Yaml::DUMP_OBJECT_AS_MAP),
                    true
                );
            }
        } catch (Exception $exception) {
            $this->markTestSkipped($exception->getMessage());
        }
    }

    /**
     * @param array<string,mixed> $overrides
     */
    protected function mergeSiteConfiguration(
        string $identifier,
        array $overrides,
    ): void {
        $siteConfiguration = $this->get(SiteConfiguration::class);
        $siteWriter = $this->get(SiteWriter::class);
        $configuration = $siteConfiguration->load($identifier);
        $configuration = array_merge($configuration, $overrides);
        try {
            $siteWriter->write($identifier, $configuration);
        } catch (Exception $exception) {
            $this->markTestSkipped($exception->getMessage());
        }
    }

    /**
     * @return array<string,mixed>
     */
    protected function buildSiteConfiguration(
        int $rootPageId,
        string $base = '',
    ): array {
        return [
            'rootPageId' => $rootPageId,
            'base'       => $base,
        ];
    }

    /**
     * @return array<string,mixed>
     */
    protected function buildDefaultLanguageConfiguration(
        string $identifier,
        string $base,
    ): array {
        $configuration = $this->buildLanguageConfiguration($identifier, $base);
        $configuration['flag'] = 'global';
        unset($configuration['fallbackType'], $configuration['fallbacks']);

        return $configuration;
    }

    /**
     * @param array<int,mixed> $fallbackIdentifiers
     *
     * @return array<string,mixed>
     */
    protected function buildLanguageConfiguration(
        string $identifier,
        string $base,
        array $fallbackIdentifiers = [],
        ?string $fallbackType = null,
    ): array {
        $preset = $this->resolveLanguagePreset($identifier);

        $configuration = [
            'languageId'      => $preset['id'],
            'title'           => $preset['title'],
            'navigationTitle' => $preset['title'],
            'websiteTitle'    => $preset['websiteTitle'] ?? '',
            'base'            => $base,
            'locale'          => $preset['locale'],
            'flag'            => $preset['iso'] ?? '',
            'fallbackType'    => $fallbackType ?? (empty($fallbackIdentifiers) ? 'strict' : 'fallback'),
        ];

        if (!empty($fallbackIdentifiers)) {
            $fallbackIds = array_map(
                function (string $fallbackIdentifier) {
                    $preset = $this->resolveLanguagePreset($fallbackIdentifier);

                    return $preset['id'];
                },
                $fallbackIdentifiers
            );
            $configuration['fallbackType'] = $fallbackType ?? 'fallback';
            $configuration['fallbacks'] = implode(',', $fallbackIds);
        }

        return $configuration;
    }

    /**
     * @param array<int,mixed> $codes
     *
     * @return array<mixed>
     */
    protected function buildErrorHandlingConfiguration(
        string $handler,
        array $codes,
    ): array {
        if ('Page' === $handler) {
            // This implies you cannot test both 404 and 403 in the same test.
            // Fixing that requires much deeper changes to the testing harness,
            // as the structure here is only a portion of the config array structure.
            if (in_array(404, $codes, true)) {
                $baseConfiguration = [
                    'errorContentSource' => 't3://page?uid=404',
                ];
            } elseif (in_array(403, $codes, true)) {
                $baseConfiguration = [
                    'errorContentSource' => 't3://page?uid=403',
                ];
            }
        } elseif ('Fluid' === $handler) {
            $baseConfiguration = [
                'errorFluidTemplate'          => 'typo3/sysext/core/Tests/Functional/Fixtures/Frontend/FluidError.fluid.html',
                'errorFluidTemplatesRootPath' => '',
                'errorFluidLayoutsRootPath'   => '',
                'errorFluidPartialsRootPath'  => '',
            ];
        // } elseif ('PHP' === $handler) {
        //     $baseConfiguration = [
        //         'errorPhpClassFQCN' => PhpError::class,
        //     ];
        } else {
            throw new LogicException(
                sprintf('Invalid handler "%s"', $handler),
                1533894782
            );
        }

        $baseConfiguration['errorHandler'] = $handler;

        return array_map(
            static function (int $code) use ($baseConfiguration) {
                $baseConfiguration['errorCode'] = $code;

                return $baseConfiguration;
            },
            $codes
        );
    }

    /**
     * @return array<string,mixed>
     */
    protected function resolveLanguagePreset(string $identifier)
    {
        if (!isset(static::LANGUAGE_PRESETS[$identifier])) {
            throw new LogicException(
                sprintf('Undefined preset identifier "%s"', $identifier),
                1533893665
            );
        }

        return static::LANGUAGE_PRESETS[$identifier];
    }

    /**
     * @todo Instruction handling should be part of Testing Framework (multiple instructions per identifier, merge in interface)
     */
    protected function applyInstructions(
        InternalRequest $request,
        InstructionInterface ...$instructions,
    ): InternalRequest {
        $modifiedInstructions = [];

        foreach ($instructions as $instruction) {
            $identifier = $instruction->getIdentifier();
            if (isset($modifiedInstructions[$identifier]) || null !== $request->getInstruction($identifier)) {
                $current = $modifiedInstructions[$identifier] ?? $request->getInstruction($identifier);
                assert($current instanceof InstructionInterface);
                $modifiedInstructions[$identifier] = $this->mergeInstruction(
                    $current,
                    $instruction
                );
            } else {
                $modifiedInstructions[$identifier] = $instruction;
            }
        }

        return $request->withInstructions($modifiedInstructions);
    }

    protected function mergeInstruction(
        InstructionInterface $current,
        InstructionInterface $other,
    ): InstructionInterface {
        if (get_class($current) !== get_class($other)) {
            throw new LogicException('Cannot merge different instruction types', 1565863174);
        }

        if ($current instanceof TypoScriptInstruction) {
            /** @var TypoScriptInstruction $other */
            $typoScript = array_replace_recursive(
                $current->getTypoScript() ?? [],
                $other->getTypoScript() ?? []
            );
            $constants = array_replace_recursive(
                $current->getConstants() ?? [],
                $other->getConstants() ?? []
            );
            if ([] !== $typoScript) {
                $current = $current->withTypoScript($typoScript);
            }
            if ([] !== $constants) {
                $current = $current->withConstants($constants);
            }

            return $current;
        }

        if ($current instanceof ArrayValueInstruction) {
            /** @var ArrayValueInstruction $other */
            $array = array_merge_recursive($current->getArray(), $other->getArray());

            return $current->withArray($array);
        }

        return $current;
    }
}
