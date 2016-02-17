<?php

defined('TYPO3_MODE') or die('Access denied.');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Plan2net.' . $_EXTKEY,
    'NewsWorkflow',
    'News Workflow'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler(
    'WorkflowController::fetchRequest',
    'Plan2net\\NewsWorkflow\\Controller\\WorkflowController->success'
);