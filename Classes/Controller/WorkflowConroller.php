<?php

namespace Plan2net\NewsWorkflow\Controller;

/**
 * Class WorkflowController
 * @package Plan2net
 * @author Christina Hauk <chauk@plan2.net>
 */
class WorkflowController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {


    // injection of Repository

    public function fetchRequest (\TYPO3\CMS\Core\Http\AjaxRequestHandler &$ajaxObj = null) {

        $success= $this->success();

        // addContent('key', 'content to add')
        // 'key' = the new content key where the content should be added in the content array
        $ajaxObj->addContent('success', $success);

    }

    public function success() {

        return "It works";
    }

    public function getButton() {

        $btn = '<button>Startseite</button>';
        return $btn;

    }
}
