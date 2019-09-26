<?php
return [

    'REPORT' => [
        'columns' => [
            'reports.name' => 10,
            'reports.description' => 5,
            'reports.state' => 8,
            'reports.category' => 7,
            'interactive_reports.summary'=> 4,
            'interactive_reports.author' => 2,
            'interactive_reports.analysts' => 2,
            'interactive_reports.leader' => 1,
            'interactive_reports.editor' => 1,
            'interactive_reports.marketing' => 1,
            'interactive_reports.key_findings_text' => 5,
            'interactive_reports.key_findings_list' => 6,

        ]
    ],

    'INTERACTIVE_REPORT' => [
        'columns' => [
            'summary' => 4,
            'author' => 2,
            'analysts' => 2,
            'leader' => 1,
            'editor' => 1,
            'marketing' => 1,
            'key_findings_text' => 5,
            'key_findings_list' => 6
        ]
    ],

    'PROFILE' => [
        'columns' => [
            'name' => 10,
            'ticker' => 5,
            'description' => 8
        ]
    ],


    'CHART' => [
        'columns' => [
            'charts.title' => 10,
            'charts.keywords' => 6,
            // 'reports.name' => 8
        ],
    ],

    'CHANNACLIP' => [
        'columns' => [
            'name' => 10,
            'description' => 8
        ]
    ],


    'BLOG' => [
        'columns' => [
            'title' => 10,
            'description' => 6,
        ]
    ],
    'TOP5' => [
        'columns' => [
            'headline' => 10,
            'source' => 10,
            'Topic' => 8,
            'full_story' => 10
        ]
    ],
    'WEBINARS' => [
        'columns' => [
            'title' => 10,
            'description_short' => 8,
            'description_long' => 6
        ]
    ],

    'PROFILE_RESPONSE' => [
        'id' => 'id',
        'name' => 'name',
        'code' => 'code', //ticker
        'thumbnail' => 'thumbnail',  //cover
        'pdfUrl' => 'pdfUrl',
        'description' => 'description',
        'access' => 'access',
        'postedBy' => 'postedBy',
    ],


];