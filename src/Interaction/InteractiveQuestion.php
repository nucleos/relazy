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

use Nucleos\Relazy\Exception\RelazyException;

/**
 * Represents the question asked the user (formatter for InformationRequest).
 */
final class InteractiveQuestion
{
    private readonly InteractionRequest $informationRequest;

    public function __construct(InteractionRequest $informationRequest)
    {
        $this->informationRequest = $informationRequest;
    }

    public function getFormattedText(): string
    {
        if (InteractionType::CONFIRMATION === $this->informationRequest->getType()) {
            $text = 'Please confirm that ';
        } else {
            $text = 'Please provide ';
        }

        $text .= strtolower((string) $this->informationRequest->getOption('description'));

        if (InteractionType::CHOICE === $this->informationRequest->getType()) {
            $text .= "\n".$this->formatChoices(
                $this->informationRequest->getOption('choices'),
                $this->informationRequest->getOption('choices_shortcuts')
            );
        }

        // print the default if exist
        if ($this->hasDefault()) {
            $defaultVal = $this->getDefault();
            if (\is_bool($defaultVal)) {
                $defaultVal = true === $defaultVal ? 'true' : 'false';
            }

            $text .= ' (default: <info>'.$defaultVal.'</info>)';
        }

        return $text.': ';
    }

    /**
     * @param string[] $choices
     * @param string[] $shortcuts
     */
    public function formatChoices(array $choices, array $shortcuts): string
    {
        if (\count($shortcuts) > 0) {
            $shortcuts = array_flip($shortcuts);
            foreach ($shortcuts as $choice => $shortcut) {
                $shortcuts[$choice] = '<info>'.$shortcut.'</info>';
            }

            foreach ($choices as $pos => $choice) {
                $choices[$pos] = '['.$shortcuts[$choice].'] '.$choice;
            }
        }

        $text = '    '.implode(PHP_EOL.'    ', $choices);

        return $text."\nYour choice";
    }

    public function hasDefault(): bool
    {
        return null !== $this->informationRequest->getOption('default');
    }

    public function getDefault(): mixed
    {
        $default = $this->informationRequest->getOption('default');
        if (\count($shortcuts = $this->informationRequest->getOption('choices_shortcuts')) > 0) {
            foreach ($shortcuts as $shortcut => $value) {
                if ($default === $value) {
                    return $shortcut;
                }
            }
        }

        return $default;
    }

    public function isHiddenAnswer(): bool
    {
        return true === $this->informationRequest->getOption('hidden_answer');
    }

    public function getValidator(): callable
    {
        return $this->validate(...);
    }

    public function validate(mixed $value): mixed
    {
        // Replace potential shortcuts
        if (\count($shortcuts = $this->informationRequest->getOption('choices_shortcuts')) > 0) {
            if (\array_key_exists($value, $shortcuts)) {
                $value = $shortcuts[$value];
            } else {
                throw new RelazyException('Please select a value in '.json_encode(array_keys($shortcuts), JSON_THROW_ON_ERROR));
            }
        }

        // Validation
        return $this->informationRequest->validate($value);
    }
}
