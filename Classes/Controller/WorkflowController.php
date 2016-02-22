<?php

namespace Plan2net\NewsWorkflow\Controller;

/**
 * Class WorkflowController
 * @package Plan2net
 * @author Christina Hauk <chauk@plan2.net>
 */
class WorkflowController {


    // injection of Repository

    /**
     * @param array $params
     * @param \TYPO3\CMS\Core\Http\AjaxRequestHandler|null $ajaxObj
     */
    public function renderAjax ($params = array(), \TYPO3\CMS\Core\Http\AjaxRequestHandler &$ajaxObj = null) {

        $success= $this->copyNews();
        $ajaxObj->addContent('success', $success);
    }

    public function copyNews() {



        return "It works";
    }

    public function getButton() {
        $path = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('news_workflow') . 'Resources/Public/Javascript/main.js';
        $trans = \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate('release', 'news_workflow');
        $script = '<script src="'. $path .'"></script>';
        $btn = '<button onclick="ajaxCall(); return false;">'. $trans .'</button><div class="msg"></div>' . $script;
        return $btn;
    }
}
