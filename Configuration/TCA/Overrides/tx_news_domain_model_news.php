<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

$temporaryColumn = array(
    'notification' => array(
        'exclude' => 0,
        'label' => 'LLL:EXT:news_workflow/Resources/Private/Language/locallang.xlf:publicDisplay',
        'config' => array(
            'type' => 'user',
            'userFunc' => 'Plan2net\NewsWorkflow\Controller\WorkflowController->getButton'
        )
    )
);


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    "tx_news_domain_model_news",
    $temporaryColumn
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    "tx_news_domain_model_news", "--div--;LLL:EXT:news_workflow/Resources/Private/Language/locallang.xlf:notification, notification"
);
