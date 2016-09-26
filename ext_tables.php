<?php

defined('TYPO3_MODE') or die('Access denied.');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler(
    'WorkflowController::copyNews',
    'Plan2net\\NewsWorkflow\\Controller\\WorkflowController->renderAjax'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'News Workflow');
