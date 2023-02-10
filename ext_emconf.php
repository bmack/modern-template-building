<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Modern Template Building - A historic TYPO3 piece',
    'description' => 'Ships cObjects TEMPLATE and FILE to build sites with marker-based templates for TYPO3',
    'category' => 'fe',
    'author' => 'Benni Mack',
    'author_email' => 'typo3@b13.com',
    'author_company' => 'b13 GmbH',
    'state' => 'stable',
    'clearCacheOnLoad' => true,
    'version' => '1.1.0',
    'constraints' => [
            'depends' => [
            'typo3' => '12.0.0-12.3.99'
        ],
        'conflicts' => [],
        'suggests' => []
    ]
];
