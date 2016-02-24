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
    ),
    'author' => array(
        'exclude' => 1,
        'l10n_mode' => 'mergeIfNotBlank',
        'label' => 'LLL:EXT:cms/locallang_tca.xlf:pages.author_formlabel',
        'config' => array(
            'type' => 'input',
            'size' => 30,
            'eval' => 'required', // because news workflow needs a person that can be contacted after publishing a record
        )
    ),
);


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    "tx_news_domain_model_news",
    $temporaryColumn
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    "tx_news_domain_model_news", "--div--;LLL:EXT:news_workflow/Resources/Private/Language/locallang.xlf:notification, notification"
);
