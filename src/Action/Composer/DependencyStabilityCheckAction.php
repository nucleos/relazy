<?php

/*
 * This file is part of the Relazy package.
 *
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nucleos\Relazy\Action\Composer;

use Nucleos\Relazy\Context;
use Nucleos\Relazy\Exception\CommandException;
use Nucleos\Relazy\Output\Console;

final class DependencyStabilityCheckAction extends AbstractAction
{
    private const DEPENDENCY_LISTS = ['require', 'require-dev'];

    /**
     * @var string[]
     */
    private readonly array $ignore;

    /**
     * @var string[]
     */
    private array $allowList = [];

    /**
     * @var array<string, string[]>
     */
    private array $dependenciesAllowList = [];

    /**
     * @param string[]|null $allowList
     * @param string[]|null $ignoreList
     */
    public function __construct(?array $allowList = null, ?array $ignoreList = null)
    {
        $this->ignore = $ignoreList ?? [];

        if (null !== $allowList) {
            $this->createAllowLists($allowList);
        }
    }

    public function execute(Context $context, Console $console): void
    {
        $contents = $this->getContent();

        foreach (self::DEPENDENCY_LISTS as $dependencyList) {
            if (!$this->isListIgnored($dependencyList) && $this->listExists($contents, $dependencyList)) {
                $specificAllowList = $this->generateListSpecificAllowList($dependencyList);
                $this->checkDependencies($contents[$dependencyList], $specificAllowList);
            }
        }
    }

    /**
     * @param string[] $allowListConfig
     */
    private function createAllowLists(array $allowListConfig): void
    {
        foreach ($allowListConfig as $listing) {
            if (isset($listing[1])) {
                if (!\in_array($listing[1], self::DEPENDENCY_LISTS, true)) {
                    throw new CommandException(sprintf(
                        'configuration error: %s is no valid composer dependency section',
                        $listing[1]
                    ));
                }

                if (!isset($this->dependenciesAllowList[$listing[1]])) {
                    $this->dependenciesAllowList[$listing[1]] = [];
                }

                $this->dependenciesAllowList[$listing[1]][] = $listing[0];
            } else {
                $this->allowList[] = $listing[0];
            }
        }
    }

    private function isListIgnored(string $dependencyList): bool
    {
        return \in_array($dependencyList, $this->ignore, true);
    }

    /**
     * @param array<string, mixed> $contents
     */
    private function listExists(array $contents, string $dependencyList): bool
    {
        return isset($contents[$dependencyList]);
    }

    /**
     * @return string[]
     */
    private function generateListSpecificAllowList(string $dependencyList): array
    {
        if (isset($this->dependenciesAllowList[$dependencyList])) {
            return array_merge($this->allowList, $this->dependenciesAllowList[$dependencyList]);
        }

        return $this->allowList;
    }

    /**
     * @param array<string, string> $dependencyList
     * @param string[]              $allowList
     */
    private function checkDependencies(array $dependencyList, array $allowList = []): void
    {
        foreach ($dependencyList as $dependency => $version) {
            if (!$this->startsWith($version, 'dev-') && !$this->endsWith($version, '@dev')) {
                continue;
            }

            if (\in_array($dependency, $allowList, true)) {
                continue;
            }

            throw new CommandException(sprintf(
                '%s uses dev-version but is not listed on allowList ',
                $dependency
            ));
        }
    }

    private function startsWith(string $haystack, string $needle): bool
    {
        return $haystack[0] === $needle[0] ? str_starts_with($haystack, $needle) : false;
    }

    private function endsWith(string $haystack, string $needle): bool
    {
        return '' === $needle || str_ends_with($haystack, $needle);
    }
}
