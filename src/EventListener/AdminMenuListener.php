<?php

namespace Disjfa\UserBundle\EventListener;

use Disjfa\MenuBundle\Menu\ConfigureAdminMenu;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
final class AdminMenuListener
{
    public function __invoke(ConfigureAdminMenu $event)
    {
        $event->getMenu()->addChild('User', ['route' => 'disjfa_user_admin_user_index']);
    }
}
