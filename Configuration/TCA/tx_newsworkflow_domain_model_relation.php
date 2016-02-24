<?php

 return array(
    'ctrl' => array (
        'label' => 'News Workflow',
        'title' => 'News Workflow Records',
    ),
    'interface' => array(
        'showRecordFieldList' => 'uid_news,uid_news_original'
    ),
    'columns' => array (
        'uid_news' => array(
            'exclude' => 0,
            'label' => 'uid-news',
            'config' => array(
                'type' => 'passthrough'
            )
        ),
        'uid_news_original' => array(
            'exclude' => 0,
            'label' => 'uid_news_original',
            'config' => array(
                'type' => 'passthrough'
            )
        ),
        'crdate' => array(
            'label' => 'crdate',
            'config' => array(
                'type' => 'passthrough'
            )
        ),
    ),
    'types' => array (
        '0' => array(
            'showitem' => 'uid_news,uid_news_original'
        ),
    ),
);




