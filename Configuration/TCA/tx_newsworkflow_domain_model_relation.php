<?php

 return array(
    'ctrl' => array (
        'label' => 'News Workflow',
        'title' => 'News Workflow Records',
    ),
    'interface' => array(
        'showRecordFieldList' => 'uid_news,uid_news_original, pid_target,author'
    ),
    'columns' => array (
        'uid_news' => array(
            'exclude' => 0,
            'label' => 'uid-news',
            'config' => array(
                'type' => 'input',
                'readOnly' => true
            )
        ),
        'uid_news_original' => array(
            'exclude' => 0,
            'label' => 'uid_news_original',
            'config' => array(
                'type' => 'input',
                'readOnly' => true
            )
        ),
        'crdate' => array(
            'label' => 'crdate',
            'config' => array(
                'type' => 'input',
                'readOnly' => true
            )
        ),
        'pid_target' => array(
            'exclude' => 0,
            'label' => 'uid-news',
            'config' => array(
                'type' => 'input',
                'readOnly' => true
            )
        ),
        'send_mail' => array(
            'exclude' => 0,
            'label' => 'uid-news',
            'config' => array(
                'type' => 'input',
                'readOnly' => true
            )
        ),
        'release_person' => array (
            'exclude' => 0,
            'label' => 'release-person',
            'config' => array (
                'type' => 'input',
                'readOnly' => true
            )
        ),
        'compare_hash' => array(
            'exclude' => 0,
            'label' => 'compare-hash',
            'config' => array(
                'type' => 'input',
                'readOnly' => true
            )
        ),
    ),
    'types' => array (
        '0' => array(
            'showitem' => 'uid_news,uid_news_original,pid_target, author'
        ),
    ),
);




