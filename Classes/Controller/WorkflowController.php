<?php

namespace Plan2net\NewsWorkflow\Controller;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class WorkflowController
 * @package Plan2net
 * @author Christina Hauk <chauk@plan2.net>
 */
class WorkflowController {

    const ERROR_NO_CONFIGURATION = 100;

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager
     */
    protected $objectManager;

    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;

    /**
     * @var array
     */
    protected $configuration;

    /**
     * @param array $params
     * @param \TYPO3\CMS\Core\Http\AjaxRequestHandler|null $ajaxObj
     */
    public function renderAjax ($params = array(), \TYPO3\CMS\Core\Http\AjaxRequestHandler &$ajaxObj = null) {
        $newsId = (integer) GeneralUtility::_GET('newsId');
        try {
            $success = $this->copyNews($newsId);

            if($success) {
                $ajaxObj->addContent('success', LocalizationUtility::translate('success_msg', 'news_workflow'));

            } else {
                $ajaxObj->setError(LocalizationUtility::translate('error_msg', 'news_workflow'));
            }
        }
        catch (\Exception $e) {
            if ($e->getCode()) {
                $this->getLogger()->error($e->getMessage());
                $ajaxObj->addContent('error', $e->getMessage());
            }
        }
    }

    /**
     * @param integer $id
     * @return string
     */
    public function copyNews($id) {
        $objectManager = $this->getObjectManager();
        /** @var \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler */
        $dataHandler = $objectManager->get('TYPO3\CMS\Core\DataHandling\DataHandler');

        /** @var \GeorgRinger\News\Domain\Repository\NewsRepository $newsRepository */
        $newsRepository = $objectManager->get('GeorgRinger\News\Domain\Repository\NewsRepository');
        // get query settings and remove all constraints (to get ALL records)
        $querySettings = $newsRepository->createQuery()->getQuerySettings();
        $querySettings->setIgnoreEnableFields(true); // ignore hidden and deleted
        $querySettings->setRespectStoragePage(false); // ignore storage pid
        $newsRepository->setDefaultQuerySettings($querySettings);


        $originalNews = $newsRepository->findByUid($id, false); // we have to explicitly set respectEnableFields to false here again

        if ($originalNews !== null) {
            try {
                $configuration = $this->getConfiguration($originalNews->getPid());
            }
            catch(\Exception $e) {
                throw $e; // re-throw
            }
            // copy news to same pid (folder)
            $dataHandler->start(array(), array());
            $dataHandler->cmdmap['tx_news_domain_model_news'][$id]['copy'] = (integer)$configuration['approvalTargetPid'];
            $dataHandler->process_cmdmap();
            // result: array ( 'original-id' => 'copied-id')
            $copyActionInformation = $dataHandler->copyMappingArray_merged['tx_news_domain_model_news'];

            if (is_array($copyActionInformation)) {
                foreach ($copyActionInformation as $originalNewsId => $copyNewsId) {
                    $copiedNews = $newsRepository->findByUid($copyNewsId, false);
                    $copiedNews->setHidden(true);
                    $this->addApprovalCategories($copiedNews, $configuration['approvalCategories']); // category ids to add
                    $newsRepository->add($copiedNews);
                }
            }

            if (empty($dataHandler->errorLog)) {

                foreach ($copyActionInformation as $uidNewsOriginal => $uidNews) {
                    $this->setWorkflowRelation($uidNews, $uidNewsOriginal, (integer)$configuration['approvalTargetPid']);
                }
                return true;
            }
            else {
                return false;
            }
        }
    }

    /**
     * @param $params
     * @return string
     */
    public function getButton($params) {
        $newsRecordUid = (integer)$params['row']['uid'];
        $path = ExtensionManagementUtility::extRelPath('news_workflow') . 'Resources/Public/Javascript/main.js';
        $trans = LocalizationUtility::translate('release', 'news_workflow');
        $trans2 = LocalizationUtility::translate('alreadyReleased', 'news_workflow');
        $script = '<script src="' . $path . '"></script>';

        $isCopied = $this->isRecordAlreadyCopied($newsRecordUid);

        if($isCopied) {
            $btn = '<button onclick="ajaxCall(' . $newsRecordUid . ',this); return false;" style="background:white;color:#D3D3D3;border:none;" disabled>' . $trans2 . $script;
        } else {
            $btn = '<button onclick="ajaxCall(' . $newsRecordUid . ',this); return false;">' . $trans . $script;
        }

        return $btn;
    }


    /**
     * @param \GeorgRinger\News\Domain\Model\News $news
     * @param $categoryIds
     */
    protected function addApprovalCategories($news, $categoryIds) {
        $objectManager = $this->getObjectManager();
        /** @var \GeorgRinger\News\Domain\Repository\CategoryRepository $categoryRepository */
        $categoryRepository = $objectManager->get('GeorgRinger\News\Domain\Repository\CategoryRepository');

        if (!is_array($categoryIds)) {
            $categoryIds = GeneralUtility::trimExplode(',', $categoryIds);
        }
        foreach ($categoryIds as $categoryId) {
            /** @var \GeorgRinger\News\Domain\Model\Category $category */
            $category = $categoryRepository->findByUid($categoryId);
            if ($category !== null) {
                $news->addCategory($category);
            }
        }
    }

    /**
     * @param integer $pageId
     * @return array
     * @throws \Exception
     */
    protected function getConfiguration($pageId) {
        if (empty($this->configuration)) {
            $pageTsSettings = \TYPO3\CMS\Backend\Utility\BackendUtility::getPagesTSconfig($pageId);
            if (isset($pageTsSettings['user.']['tx_news_workflow.'])) {
                $pageTsSettings = $pageTsSettings['user.']['tx_news_workflow.'];
            }
            else {
                throw new \Exception('No page TypoScript settings for user.tx_news_workflow found', self::ERROR_NO_CONFIGURATION);
            }
            $this->configuration = $pageTsSettings;
        }

        return $this->configuration;
    }

    /**
     * @return object|\TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected function getObjectManager() {
        if ($this->objectManager === null) {
            $this->objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        }

        return $this->objectManager;
    }

    /**
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger()
    {
        if ($this->logger === null) {
            $this->logger = GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
        }

        return $this->logger;
    }

    /**
     * @param $uidNews
     * @param $uidNewsOriginal
     * @param int $pid
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    protected function setWorkflowRelation ($uidNews, $uidNewsOriginal, $pid = 0)
    {

        $objectManager = $this->getObjectManager();

        /** @var \Plan2net\NewsWorkflow\Domain\Repository\RelationRepository $relationRepository */
        $relationRepository = $objectManager->get('Plan2net\NewsWorkflow\Domain\Repository\RelationRepository');

        /** @var \Plan2net\NewsWorkflow\Domain\Model\Relation $relation */
        $relation = $objectManager->get('Plan2net\NewsWorkflow\Domain\Model\Relation');

        $relation->setUidNews($uidNews);
        $relation->setUidNewsOriginal($uidNewsOriginal);
        $relation->setPidTarget($pid);
        $relation->setPid($pid);
        $relation->setDateCreated(time());
        $relation->setSendMail(0);

        $relationRepository->add($relation);
        $relationRepository->persistAll(); // write to database immediately
    }

    /**
     * @param $uid
     * @return bool
     */
    protected function isRecordAlreadyCopied($uid) {

        $objectManager = $this->getObjectManager();

        /** @var \Plan2net\NewsWorkflow\Domain\Repository\RelationRepository $relationRepository */
        $relationRepository = $objectManager->get('Plan2net\NewsWorkflow\Domain\Repository\RelationRepository');

        $querySettings = $relationRepository->createQuery()->getQuerySettings();
        $querySettings->setIgnoreEnableFields(true); // ignore hidden and deleted
        $querySettings->setRespectStoragePage(false); // ignore storage pid
        $relationRepository->setDefaultQuerySettings($querySettings);
        $record = $relationRepository->findOriginalRecord($uid);

        if(is_object($record)) {
            return true;
        } else {
            return false;
        }

    }

}
