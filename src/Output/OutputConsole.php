<?php

/*
 * This file is part of the Relazy package.
 *
 * (c) Christian Gripp <mail@core23.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nucleos\Relazy\Output;

use Nucleos\Relazy\Interaction\InteractiveQuestion;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

final class OutputConsole implements Console
{
    private FormatterHelper $formatterHelper;

    private QuestionHelper $dialogHelper;

    private int $indentationLevel      = 0;

    private int $indentationSize       = 4;

    private bool $positionIsALineStart = true;

    private readonly InputInterface $input;

    private readonly OutputInterface $output;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input  = $input;
        $this->output = $output;

        $formatter = $output->getFormatter();
        $formatter->setStyle('title', new OutputFormatterStyle('black', 'white'));
        $formatter->setStyle('question', new OutputFormatterStyle('black', 'cyan'));
        $formatter->setStyle('error', new OutputFormatterStyle('white', 'red'));
        $formatter->setStyle('warning', new OutputFormatterStyle('white', 'blue'));
        $formatter->setStyle('green', new OutputFormatterStyle('green'));
        $formatter->setStyle('yellow', new OutputFormatterStyle('yellow'));
    }

    public function getOutput(): OutputInterface
    {
        return $this->output;
    }

    public function indent(int $repeat = 1): void
    {
        $this->indentationLevel += $repeat;
    }

    public function unindent(int $repeat = 1): void
    {
        $this->indentationLevel -= $repeat;
    }

    public function resetIndentation(): void
    {
        $this->indentationLevel = 0;
    }

    public function setDialogHelper(QuestionHelper $dh): void
    {
        $this->dialogHelper = $dh;
    }

    public function setFormatterHelper(FormatterHelper $fh): void
    {
        $this->formatterHelper = $fh;
    }

    public function writeTitle(string $title): void
    {
        $this->writeLine();
        $this->internalWrite($this->formatterHelper->formatBlock($title, 'title'), true);
        $this->writeLine();
    }

    public function write(string $text): void
    {
        $this->internalWrite($text, false);
    }

    public function writeLine(string $text = ''): void
    {
        $this->internalWrite($text, true);
    }

    public function writeWarning(string $text): void
    {
        $this->internalWrite(sprintf('<warning>%s</warning>', $text), true);
    }

    public function writeError(string $text): void
    {
        $this->internalWrite(sprintf('<error>%s</error>', $text), true);
    }

    public function askQuestion(InteractiveQuestion $question, ?int $position, InputInterface $input): mixed
    {
        $text = (null !== $position ? $position.') ' : null).$question->getFormattedText();

        $q = new Question($text, $question->getDefault());
        $q->setValidator($question->getValidator());
        if ($question->isHiddenAnswer()) {
            $q->setHidden(true);
        }

        return $this->dialogHelper->ask($input, $this->output, $q);
    }

    public function confirm(string $text): bool
    {
        return $this->dialogHelper->ask($this->input, $this->output, new ConfirmationQuestion($text));
    }

    private function internalWrite(string $message, bool $newline): void
    {
        $message = str_replace(PHP_EOL, PHP_EOL.$this->getIndentPadding(), $message);

        if ($this->positionIsALineStart) {
            $message = $this->getIndentPadding().$message;
        }

        $this->positionIsALineStart = $newline;

        if ($newline) {
            $this->output->writeln($message);
        } else {
            $this->output->write($message);
        }
    }

    private function getIndentPadding(): string
    {
        return str_pad('', $this->indentationLevel * $this->indentationSize);
    }
}
