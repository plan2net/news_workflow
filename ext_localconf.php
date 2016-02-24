<?php

defined('TYPO3_MODE') or die('Access denied.');

if (TYPO3_MODE == 'BE') {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][] = 'Plan2net\NewsWorkflow\Command\EmailCommandController';
}





