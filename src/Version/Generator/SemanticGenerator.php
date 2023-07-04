<?php

/*
 * This file is part of the Relazy package.
 *
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nucleos\Relazy\Version\Generator;

use Nucleos\Relazy\Context;
use Nucleos\Relazy\Exception\CommandOrderException;
use Nucleos\Relazy\Exception\RelazyException;
use Nucleos\Relazy\Interaction\InteractionRequest;
use Nucleos\Relazy\Interaction\InteractionRequestAware;
use Nucleos\Relazy\Interaction\InteractionType;
use Nucleos\Relazy\Version\ReleaseType;
use vierbergenlars\SemVer\version;

/**
 * Generator based on the Semantic Versioning defined by Tom Preston-Werner
 * Description available here: https://semver.org/.
 */
final class SemanticGenerator implements Generator, InteractionRequestAware
{
    private const TYPE = 'type';

    private const LABEL = 'label';

    private readonly ?string $label;

    private readonly bool $allowLabel;

    private readonly ?string $type;

    public function __construct(?string $label = null, ?bool $allowLabel = null, ?string $type = null)
    {
        $this->label      = $label;
        $this->allowLabel = $allowLabel ?? false;
        $this->type       = $type;
    }

    public function generateNextVersion(Context $context): string
    {
        $type = ReleaseType::from($this->type ?? $context->getInformationCollection()->getValue(self::TYPE));

        $label = 'none';

        if ($this->allowLabel) {
            $label = $this->label ?? $context->getInformationCollection()->getValue(self::LABEL);
        }

        $currentVersion = $context->getCurrentVersion();

        if (null === $currentVersion) {
            throw CommandOrderException::forField('current version');
        }

        $this->assertValidVersion($currentVersion);

        preg_match('$(?:(\d+\.\d+\.\d+)(?:(-)([a-zA-Z]+)(\d+)?)?)$', $currentVersion, $matches);

        // if last version is with label
        if (\count($matches) > 3) {
            [$major, $minor, $patch] = explode('.', $currentVersion);

            $pos   = strpos($patch, '-');

            if (false !== $pos) {
                $patch = substr($patch, 0, $pos);
            }

            if ('none' !== $label) {
                $labelVersion = '';

                // increment label
                if (\array_key_exists(3, $matches)) {
                    $oldLabel     = $matches[3];
                    $labelVersion = 2;

                    // if label is new clear version
                    if ($label !== $oldLabel) {
                        $labelVersion = '';
                    } elseif (\array_key_exists(4, $matches)) {
                        // if version exists increment it
                        $labelVersion = (int) $matches[4] + 1;
                    }
                }

                return implode('.', [$major, $minor, $patch]).'-'.$label.$labelVersion;
            }

            return implode('.', [$major, $minor, $patch]);
        }

        [$major, $minor, $patch] = explode('.', $currentVersion);

        // Increment
        switch ($type) {
            case ReleaseType::MAJOR:
                $major = (int) $major +1;
                $patch = 0;
                $minor = 0;

                break;

            case ReleaseType::MINOR:
                $minor = (int) $minor +1;
                $patch = 0;

                break;

            default:
                $patch = (int) $patch +1;

                break;
        }

        // new label
        if ('none' !== $label) {
            return implode('.', [$major, $minor, $patch]).'-'.$label;
        }

        return implode('.', [$major, $minor, $patch]);
    }

    public function getInteractionRequest(): array
    {
        $list = [];

        if (null === $this->type) {
            $list[] = new InteractionRequest(self::TYPE, InteractionType::CHOICE, [
                'description'       => 'release type',
                'choices'           => ['major', 'minor', 'patch'],
                'choices_shortcuts' => ['m' => 'major', 'i' => 'minor', 'p' => 'patch'],
                'default'           => 'patch',
            ]);
        }

        if (!$this->allowLabel) {
            return $list;
        }

        if (null !== $this->label) {
            return $list;
        }

        $list[] = new InteractionRequest(self::LABEL, InteractionType::CHOICE, [
            'description'       => 'release label',
            'choices'           => ['rc', 'beta', 'alpha', 'none'],
            'choices_shortcuts' => ['rc' => 'rc', 'b' => 'beta', 'a' => 'alpha', 'n' => 'none'],
            'default'           => 'none',
        ]);

        return $list;
    }

    public function getInitialVersion(): string
    {
        return '0.0.0';
    }

    public function compareVersions(string $a, string $b): int
    {
        return version::compare($a, $b);
    }

    public function getValidationRegex(): string
    {
        return '(?:(\d+\.\d+\.\d+)(?:(-)([a-zA-Z]+)(\d+)?)?)';
    }

    private function assertValidVersion(string $currentVersion): void
    {
        if (1 !== preg_match('#^'.$this->getValidationRegex().'$#', $currentVersion)) {
            throw new RelazyException(
                sprintf('Current version format is invalid (%s). It should be major.minor.patch', $currentVersion)
            );
        }
    }
}
