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
        'Titirangi',
        'North Shore',
    ],

    /*
    | Regular night series. Pick one when creating an event in admin.
    */
    'series' => [
        'football' => [
            'label' => 'Footy',
            'emoji' => '⚽',
            'blurb' => '5-a-side every week. Show up solo or bring a mate.',
        ],
        'quiz' => [
            'label' => 'Pub quizzes',
            'emoji' => '🧠',
            'blurb' => 'Quiz tables at the pub. We mix teams so nobody sits with the same crew.',
        ],
        'padel' => [
            'label' => 'Padel',
            'emoji' => '🎾',
            'blurb' => 'Courts booked, groups rotated. Fine if you have never played before.',
        ],
        'games' => [
            'label' => 'Game nights',
            'emoji' => '🎲',
            'blurb' => 'Board games, cards, consoles. Drop in even if you do not know the rules.',
        ],
        'bowling' => [
            'label' => 'Bowling',
            'emoji' => '🎳',
            'blurb' => 'Lanes booked, shoes sorted. Easy night if sport is not your thing.',
        ],
        'golf' => [
            'label' => 'Golf outings',
            'emoji' => '⛳',
            'blurb' => 'Nine holes or the range. Mixed ability, no low-handicap gatekeeping.',
        ],
        'running' => [
            'label' => 'Running groups',
            'emoji' => '🏃',
            'blurb' => 'Group runs around the city. Pace groups so nobody gets dropped.',
        ],
        'hiking' => [
            'label' => 'Hiking trips',
            'emoji' => '🥾',
            'blurb' => 'Weekend trails outside Auckland. Car pools sorted on the day.',
        ],
    ],

];
