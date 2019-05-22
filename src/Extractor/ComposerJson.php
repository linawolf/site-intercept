<?php
declare(strict_types = 1);

/*
 * This file is part of the package t3g/intercept.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace App\Extractor;

use App\Exception\Composer\DocsComposerMissingValueException;
use Composer\Semver\Semver;

/**
 * Contains contents of the composer.json
 */
class ComposerJson
{
    private const ALLOWED_TYPO_VERSIONS = ['6.2', '7.6', '8.7', '9.5'];

    /**
     * @var array
     */
    private $composerJson;

    public function __construct(array $composerJson)
    {
        $this->composerJson = $composerJson;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        $this->assertPropertyContainsValue('name');
        return (string)$this->composerJson['name'];
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        $this->assertPropertyContainsValue('type');
        return (string)$this->composerJson['type'];
    }

    /**
     * @param string $packageName
     * @return bool
     */
    public function requires(string $packageName): bool
    {
        return isset($this->composerJson['require'][$packageName]);
    }

    /**
     * @return array
     */
    public function getFirstAuthor(): array
    {
        $this->assertPropertyContainsValue('authors');
        return current($this->composerJson['authors']);
    }

    /**
     * @return string
     * @throws DocsComposerMissingValueException
     */
    public function getMinimumTypoVersion(): string
    {
        if ($this->getType() !== 'typo3-cms-extension') {
            return '';
        }
        if (!$this->requires('typo3/cms-core')) {
            throw new DocsComposerMissingValueException('typo3/cms-core must be required in the composer json, but was not found', 1558084137);
        }

        return $this->extractTypoVersion();
    }

    /**
     * @return string
     * @throws DocsComposerMissingValueException
     */
    public function getMaximumTypoVersion(): string
    {
        if ($this->getType() !== 'typo3-cms-extension') {
            return '';
        }
        if (!$this->requires('typo3/cms-core')) {
            throw new DocsComposerMissingValueException('typo3/cms-core must be required in the composer json, but was not found', 1558084146);
        }

        return $this->extractTypoVersion(true);
    }

    /**
     * @param bool $getMaximum
     * @return string
     */
    private function extractTypoVersion(bool $getMaximum = false): string
    {
        $maxVersion = '';
        foreach (self::ALLOWED_TYPO_VERSIONS as $typoVersion) {
            if (Semver::satisfies($typoVersion, $this->composerJson['require']['typo3/cms-core'])) {
                if (!$getMaximum) {
                    return $typoVersion;
                }
                $maxVersion = $typoVersion;
            }
        }

        return $maxVersion;
    }

    /**
     * @param string $propertyName
     * @throws DocsComposerMissingValueException
     */
    private function assertPropertyContainsValue(string $propertyName): void
    {
        if (empty($this->composerJson[$propertyName])
            || (is_string($this->composerJson[$propertyName]) && trim($this->composerJson[$propertyName]) === '')
        ) {
            throw new DocsComposerMissingValueException('Property "' . $propertyName . '" is missing or is empty in composer.json', 1557309364);
        }
    }
}
