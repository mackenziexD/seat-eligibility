<?php

return [
    'busa' => [
        'permission'    => 'global.superuser',
        'name'          => 'BUSA Package',
        'icon'          => 'fas fa-cogs',
        'route_segment' => 'busa',
        'entries'       => [
            [
                'name'  => 'Dashboard',
                'icon'  => 'fas fa-tachometer-alt',
                'route' => 'busa.dashboard',
            ]
        ],
    ],
];
