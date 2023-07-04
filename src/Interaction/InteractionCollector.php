<?php

/*
 * This file is part of the Relazy package.
 *
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nucleos\Relazy\Interaction;

use InvalidArgumentException;
use Nucleos\Relazy\Exception\RelazyException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

final class InteractionCollector implements InteractionCollection
{
    /**
     * @var array<string, InteractionRequest>
     */
    private array $requests = [];

    public function registerRequest(InteractionRequest $request): void
    {
        $name = $request->getName();

        if ($this->hasRequest($name)) {
            throw new RelazyException(sprintf('Request [%s] already registered', $name));
        }

        $this->requests[$name] = $request;
    }

    /**
     * @param InteractionRequest[] $list
     */
    public function registerRequests(iterable $list): void
    {
        foreach ($list as $request) {
            $this->registerRequest($request);
        }
    }

    public function hasValue(string $name): bool
    {
        return $this->getRequest($name)->hasValue();
    }

    /**
     * Return a set of command request, converted from the Base Request.
     *
     * @return InputOption[]
     */
    public function getCommandOptions(): array
    {
        $consoleOptions = [];
        foreach ($this->requests as $name => $request) {
            if ($request->isAvailableAsCommandOption()) {
                $consoleOptions[$name] = $request->convertToCommandOption();
            }
        }

        return $consoleOptions;
    }

    public function hasMissingInformation(): bool
    {
        foreach ($this->requests as $request) {
            if (!$request->hasValue()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, InteractiveQuestion>
     */
    public function getInteractiveQuestions(): array
    {
        $questions = [];
        foreach ($this->requests as $name => $request) {
            if ($request->isAvailableForInteractive() && !$request->hasValue()) {
                $questions[$name] = $request->createQuestion();
            }
        }

        return $questions;
    }

    public function handleCommandInput(InputInterface $input): void
    {
        foreach ($input->getOptions() as $name => $value) {
            if (!$this->hasRequest($name)) {
                continue;
            }

            if (!(null !== $value && false !== $value)) {
                continue;
            }

            $this->getRequest($name)->setValue($value);
        }
    }

    public function setValue(string $name, mixed $value): void
    {
        $this->getRequest($name)->setValue($value);
    }

    public function getValue(string $name, mixed $default = null): mixed
    {
        if ($this->hasRequest($name)) {
            return $this->getRequest($name)->getValue();
        }

        if (2 === \func_num_args()) {
            return $default;
        }

        throw new RelazyException(sprintf('No request named %s', $name));
    }

    private function hasRequest(string $name): bool
    {
        return \array_key_exists($name, $this->requests);
    }

    private function getRequest(string $name): InteractionRequest
    {
        if (!$this->hasRequest($name)) {
            throw new InvalidArgumentException(sprintf('There is no information request named [%s]', $name));
        }

        return $this->requests[$name];
    }
}
