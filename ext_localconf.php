<?php

defined('TYPO3_MODE') or die('Access denied.');

//first array = all actions, second array = action which are not being cached
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Plan2net.' . $_EXTKEY, // VendorName . Extension Key required when using namespaces
    'NewsWorkflow',
    array(
        'NewsWorkflow' => 'fetchRequest, success, getButton',
    ),
    array(
        'NewsWorkflow' => 'fetchRequest, success, getButton',
    )
);

