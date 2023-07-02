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

enum InteractionType: string
{
    case TEXT = 'text';

    case YES_NO = 'yes-no';

    case CHOICE = 'choice';

    case CONFIRMATION = 'confirmation';
}
