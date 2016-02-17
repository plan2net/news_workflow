<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "news workflow".
 *
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
    'title' => 'News Workflow',
    'description' => '',
    'category' => 'plugin',
    'author' => 'christina hauk',
    'author_email' => 'chauk@plan2.net',
    'shy' => '',
    'dependencies' => 'cms,news',
    'conflicts' => '',
    'priority' => '',
    'module' => '',
    'state' => 'beta',
    'internal' => '',
    'uploadfolder' => 0,
    'createDirs' => '',
    'modify_tables' => '',
    'clearCacheOnLoad' => 0,
    'lockType' => '',
    'author_company' => '',
    'version' => '0.1.0',
    'constraints' => array(
        'depends' => array(
            'cms' => '',
            'news' => '',
        ),
        'conflicts' => array(),
        'suggests' => array(),
    ),
    'suggests' => array(),
);
?>