<?php

return [

    /*
    | Hide public events and show a waitlist until launch. Set M8S_PRELAUNCH=false when ready.
    */
    'prelaunch' => env('M8S_PRELAUNCH', true),

    'launch_label' => 'October',

    'suburbs' => [
        'Auckland CBD',
        'Ponsonby',
        'Grey Lynn',
        'Kingsland',
        'Mt Eden',
        'Eden Terrace',
        'Freemans Bay',
        'Parnell',
        'Newmarket',
        'Epsom',
        'Remuera',
        'Takapuna',
        'Devonport',
        'K Road',
        'Sandringham',
        'Balmoral',
        'Onehunga',
        'Henderson',
        'North Shore',
    ],

    /*
    | Regular night series — pick one when creating an event in admin.
    */
    'series' => [
        'football' => [
            'label' => 'Footy night',
            'emoji' => '⚽',
            'blurb' => '5-a-side, every week. Show up solo or bring a mate.',
        ],
        'mtg' => [
            'label' => 'MTG night',
            'emoji' => '🃏',
            'blurb' => 'Commander and casual drafts. Cards optional your first time.',
        ],
        'trivia' => [
            'label' => 'Trivia night',
            'emoji' => '🧠',
            'blurb' => 'Pub quiz tables. We mix the teams so nobody sits with the same crew.',
        ],
        'bowling' => [
            'label' => 'Bowling night',
            'emoji' => '🎳',
            'blurb' => 'Lanes booked, shoes sorted. Easy night if sport is not your thing.',
        ],
        'bar' => [
            'label' => 'Bar night',
            'emoji' => '🍻',
            'blurb' => 'Corner table, a few rounds, actual conversation.',
        ],
        'pool' => [
            'label' => 'Pool night',
            'emoji' => '🎱',
            'blurb' => 'Tables in Ponsonby. Grab a pint, get rotated onto a game.',
        ],
        'hoops' => [
            'label' => 'Hoops run',
            'emoji' => '🏀',
            'blurb' => 'Sunday run at the park. Mixed ability, short games.',
        ],
    ],

];
