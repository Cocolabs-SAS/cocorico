<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Cocorico\UserBundle\Admin;

use Sonata\UserBundle\Admin\Model\GroupAdmin as SonataGroupAdmin;

class GroupAdmin extends SonataGroupAdmin
{
    protected $baseRoutePattern = 'group';
}
