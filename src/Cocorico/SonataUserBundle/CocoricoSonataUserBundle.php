<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\SonataUserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class CocoricoSonataUserBundle extends Bundle
{

    public function getParent()
    {
        return 'SonataUserBundle';
    }

}
