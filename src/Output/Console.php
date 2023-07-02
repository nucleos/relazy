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
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;

interface Console
{
    public function indent(int $repeat = 1): void;

    public function unindent(int $repeat = 1): void;

    public function resetIndentation(): void;

    public function setDialogHelper(QuestionHelper $dh): void;

    public function setFormatterHelper(FormatterHelper $fh): void;

    public function writeTitle(string $title): void;

    public function write(string $text): void;

    public function writeLine(string $text = ''): void;

    public function writeWarning(string $text): void;

    public function writeError(string $text): void;

    public function askQuestion(InteractiveQuestion $question, ?int $position, InputInterface $input): mixed;

    public function confirm(string $text): bool;
}
