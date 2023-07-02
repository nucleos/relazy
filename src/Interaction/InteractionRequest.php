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

use Exception;
use InvalidArgumentException;
use Nucleos\Relazy\Exception\RelazyException;
use Symfony\Component\Console\Input\InputOption;

/**
 * Define a user information request.
 */
final class InteractionRequest
{
    /**
     * @var array<string, mixed>
     */
    private static array $defaults   = [
        'description'               => '',
        'choices'                   => [],
        'choices_shortcuts'         => [],
        'command_argument'          => true,
        'command_shortcut'          => null,
        'interactive'               => true,
        'default'                   => null,
        'interactive_help'          => '',
        'interactive_help_shortcut' => 'h',
        'hidden_answer'             => false,
        'optional'                  => false,
    ];

    private readonly string $name;

    /**
     * @var array<string, mixed>
     */
    private array $options;

    private mixed $value;

    private bool $hasValue = false;

    private readonly InteractionType $type;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(string $name, InteractionType $type, array $options = [])
    {
        $this->name = $name;
        $this->type = $type;

        // Check for invalid option
        $invalidOptions = array_diff(array_keys($options), array_keys(self::$defaults));
        if ([] !== $invalidOptions) {
            throw new RelazyException('Invalid config option(s) ['.implode(', ', $invalidOptions).']');
        }

        // Set a default false for confirmation
        if (InteractionType::CONFIRMATION === $type) {
            $options['default'] = false;
        }

        // Merging with defaults
        $this->options = array_merge(self::$defaults, $options);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): InteractionType
    {
        return $this->type;
    }

    public function getOption(string $name): mixed
    {
        return $this->options[$name];
    }

    public function isAvailableAsCommandOption(): bool
    {
        return true === $this->options['command_argument'];
    }

    public function isAvailableForInteractive(): bool
    {
        return true === $this->options['interactive'];
    }

    public function convertToCommandOption(): InputOption
    {
        $mode = InteractionType::YES_NO === $this->type || InteractionType::CONFIRMATION === $this->type ?
            InputOption::VALUE_NONE :
            InputOption::VALUE_REQUIRED;

        return new InputOption(
            $this->name,
            $this->options['command_shortcut'],
            $mode,
            $this->options['description'],
            (!$this->isAvailableForInteractive() && InteractionType::CONFIRMATION !== $this->type) ? $this->options['default'] : null
        );
    }

    public function createQuestion(): InteractiveQuestion
    {
        return new InteractiveQuestion($this);
    }

    public function setValue(mixed $value): void
    {
        try {
            $value = $this->validate($value);
        } catch (Exception $e) {
            throw new InvalidArgumentException('Validation error for ['.$this->getName().']: '.$e->getMessage(), $e->getCode(), $e);
        }

        $this->value    = $value;
        $this->hasValue = true;
    }

    public function validate(mixed $value): mixed
    {
        switch ($this->type) {
            case InteractionType::CHOICE:
                $this->validateValue([$value, $this->options['choices']], static function ($v, $choices): bool {
                    return \in_array($v, $choices, true);
                }, 'Must be one of '.json_encode($this->options['choices'], JSON_THROW_ON_ERROR));

                break;

            case InteractionType::TEXT:
                $this->validateValue($value, function ($v): bool {
                    if (null === $v && true === $this->options['optional']) {
                        return true;
                    }

                    return \is_string($v) && '' !== $v;
                }, 'Text must be provided');

                break;

            case InteractionType::YES_NO:
                $value = lcfirst((string) $value[0]);
                $this->validateValue($value, static function ($v): bool {
                    return 'y' === $v || 'n' === $v;
                }, "Must be 'y' or 'n'");

                break;
        }

        return $value;
    }

    public function getValue(): mixed
    {
        if (!$this->hasValue() && null === $this->options['default']) {
            throw new RelazyException(sprintf('No value [%s] available', $this->name));
        }

        return $this->hasValue() ? $this->value : $this->options['default'];
    }

    public function hasValue(): bool
    {
        return $this->hasValue;
    }

    private function validateValue(mixed $parameters, callable $callback, string $message): void
    {
        if (!\is_array($parameters)) {
            $parameters = [$parameters];
        }

        if (false === \call_user_func_array($callback, $parameters)) {
            throw new InvalidArgumentException($message);
        }
    }
}
