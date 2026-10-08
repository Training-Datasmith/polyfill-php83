<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class OverrideParent
{
    public function overridden(): string
    {
        return 'parent';
    }
}

class OverrideChild extends OverrideParent
{
    #[Override]
    public function overridden(): string
    {
        return 'child-marker';
    }
}
