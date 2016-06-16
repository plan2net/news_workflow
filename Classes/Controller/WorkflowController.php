<?php

namespace Plan2net\NewsWorkflow\Controller;

use TYPO3\CMS\Core\FormProtection\Exception;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class WorkflowController
 *
 * @package Plan2net
 * @author  Christina Hauk <chauk@plan2.net>
 * @author  Wolfgang Klinger <wk@plan2.net>
 */
class WorkflowController
{

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

    public function injectObjectManager(\TYPO3\CMS\Extbase\Object\ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param array                                   $params
     * @param \TYPO3\CMS\Core\Http\AjaxRequestHandler $ajaxRequestHandler
     */
    public function renderAjax($params, \TYPO3\CMS\Core\Http\AjaxRequestHandler &$ajaxRequestHandler)
    {
        // we have to inject the object manager manually in this case
        $this->injectObjectManager(\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager'));
        $newsId = (integer)GeneralUtility::_GET('newsId');
        try {
            $this->copyNews($newsId);
            $ajaxRequestHandler->addContent('success', LocalizationUtility::translate('success_msg', 'news_workflow'));
        } catch (\Exception $e) {
            $this->getLogger()->error($e->getMessage());
            $ajaxRequestHandler->addContent('error', $e->getMessage());
        }
    }

    /**
     * @param integer $id
     * @return string
     */
    public function copyNews($id)
    {
        $newsRepository = $this->getNewsRepository();

        $originalNews = $newsRepository->findByUid($id, false); // we have to explicitly set respectEnableFields to false here again

        if ($originalNews) {
            $this->configuration = $this->getConfiguration($originalNews->getPid());
            $copyActionInformation = $this->copyNewsWithDataHandler($id);

            $this->postProcessCopiedNews($copyActionInformation, $originalNews);
        }
    }

    /**
     * @param integer $uid
     * @return array
     * @throws \Exception
     */
    protected function copyNewsWithDataHandler($uid)
    {
        $objectManager = $this->getObjectManager();
        /** @var \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler */
        $dataHandler = $objectManager->get('TYPO3\CMS\Core\DataHandling\DataHandler');
        // copy news to same pid (folder)
        $dataHandler->start(array(), array());
        $dataHandler->cmdmap['tx_news_domain_model_news'][(integer)$uid]['copy'] = (integer)$this->configuration['approvalTargetPid'];
        $dataHandler->process_cmdmap();

        if (!empty($dataHandler->errorLog)) {
            $this->logger->warning(print_r($dataHandler->errorLog, true));
            // @todo
            throw new \Exception('Something went wrong with DataHandler, see log file');
        }

        return $dataHandler->copyMappingArray_merged['tx_news_domain_model_news'];
    }

    /**
     * @param array $copyActionInformation
     * @param       $originalNews
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    protected function postProcessCopiedNews($copyActionInformation, $originalNews)
    {
        $newsRepository = $this->getNewsRepository();

        if (is_array($copyActionInformation)) {
            foreach ($copyActionInformation as $originalNewsId => $copiedNewsId) {
                $copiedNews = $newsRepository->findByUid($copiedNewsId, false);
                if ($copiedNews) {
                    $copiedNews->setHidden(true);
                    $this->addApprovalCategories(
                        $copiedNews,
                        GeneralUtility::trimExplode(',', $this->configuration['approvalCategories']),
                        (boolean)$this->configuration['removePreviousCategories']
                    );
                    $newsRepository->add($copiedNews);
                    // @todo
                    $hash = hash('md5', json_encode($originalNews));
                    $this->setWorkflowRelation($copiedNewsId, $originalNewsId, $hash, (integer)$this->configuration['approvalTargetPid']);
                } else {
                    // @todo
                    $this->logger->warning('Copied news (' . $copiedNewsId . ') not found in repository');
                }
            }
        }
    }

    protected function getNewsRepository()
    {
        /** @var \GeorgRinger\News\Domain\Repository\NewsRepository $newsRepository */
        $newsRepository = $this->objectManager->get('GeorgRinger\News\Domain\Repository\NewsRepository');

        // get query settings and remove all constraints (to get ALL records)
        $querySettings = $newsRepository->createQuery()->getQuerySettings();
        $querySettings->setIgnoreEnableFields(true); // ignore hidden and deleted
        $querySettings->setRespectStoragePage(false); // ignore storage pid
        $newsRepository->setDefaultQuerySettings($querySettings);

        return $newsRepository;
    }

    /**
     * @param $params
     * @return string
     */
    public function getButton($params)
    {
        $newsRecordUid = (integer)$params['row']['uid'];

        $isAlreadyCopied = $this->isRecordAlreadyCopied($newsRecordUid);

        if (!empty($newsRecordUid)) {
            if ($isAlreadyCopied) {
                $content = '<p>' . LocalizationUtility::translate('alreadyReleased', 'news_workflow') . '</p>';
            } else {
                $path = ExtensionManagementUtility::extRelPath('news_workflow') . 'Resources/Public/Javascript/main.js';
                $script = '<script src="' . $path . '"></script>';

                $content = '<button onclick="ajaxCall(' . $newsRecordUid . ',this);return false;">' .
                    LocalizationUtility::translate('release', 'news_workflow') . $script;
            }
        } else {
            $content = '<p>' . LocalizationUtility::translate('save_btn', 'news_workflow') . '</p>';
        }

        return $content;
    }

    /**
     * @param \GeorgRinger\News\Domain\Model\News $news
     * @param array                               $categoryIds
     * @param boolean                             $removePreviousCategories
     */
    protected function addApprovalCategories($news, $categoryIds, $removePreviousCategories = false)
    {
        $objectManager = $this->getObjectManager();
        /** @var \GeorgRinger\News\Domain\Repository\CategoryRepository $categoryRepository */
        $categoryRepository = $objectManager->get('GeorgRinger\News\Domain\Repository\CategoryRepository');

        $categories = $news->getCategories();

        if ($removePreviousCategories === true) {
            $clonedCategories = clone $categories;
            foreach ($clonedCategories as $category) {
                $categories->detach($category);
            }
        }

        foreach ($categoryIds as $categoryId) {
            /** @var \GeorgRinger\News\Domain\Model\Category $category */
            $category = $categoryRepository->findByUid($categoryId);
            if ($category) {
                $categories->attach($category);
            }
        }

        $news->setCategories($categories);
    }

    /**
     * @param integer $pageId
     * @return array
     * @throws \Exception
     */
    protected function getConfiguration($pageId)
    {
        if (empty($this->configuration)) {
            $pageTsSettings = \TYPO3\CMS\Backend\Utility\BackendUtility::getPagesTSconfig((integer)$pageId);
            if (isset($pageTsSettings['user.']['tx_news_workflow.'])) {
                $pageTsSettings = $pageTsSettings['user.']['tx_news_workflow.'];
            } else {
                throw new \Exception('No page TypoScript settings for user.tx_news_workflow found');
            }
            $this->configuration = $pageTsSettings;
        }

        return $this->configuration;
    }

    /**
     * @return object|\TYPO3\CMS\Extbase\Object\ObjectManager
     */
    protected function getObjectManager()
    {
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
     * @param integer $uidNews
     * @param integer $uidNewsOriginal
     * @param integer $pid
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    protected function setWorkflowRelation($uidNews, $uidNewsOriginal, $hash, $pid = 0)
    {
        $objectManager = $this->getObjectManager();
        $currentUserId = (integer)$GLOBALS['BE_USER']->user['uid'];

        /** @var \Plan2net\NewsWorkflow\Domain\Repository\RelationRepository $relationRepository */
        $relationRepository = $objectManager->get('Plan2net\NewsWorkflow\Domain\Repository\RelationRepository');

        /** @var \TYPO3\CMS\Extbase\Domain\Repository\BackendUserRepository $backendUserRepository */
        $backendUserRepository = $objectManager->get('TYPO3\CMS\Extbase\Domain\Repository\BackendUserRepository');

        /** @var \TYPO3\CMS\Beuser\Domain\Model\BackendUser $currentUser */
        $currentUser = $backendUserRepository->findByUid($currentUserId);

        /** @var \Plan2net\NewsWorkflow\Domain\Model\Relation $relation */
        $relation = $objectManager->get('Plan2net\NewsWorkflow\Domain\Model\Relation');

        $relation->setUidNews($uidNews);
        $relation->setUidNewsOriginal($uidNewsOriginal);
        $relation->setPidTarget($pid);
        $relation->setPid($pid);
        $relation->setDateCreated(time());
        $relation->setSendMail(false);
        $relation->setCompareHash($hash);
        $relation->setSendMailChangedRecord(false);

        $relation->setReleasePerson($currentUser);

        $relationRepository->add($relation);
        $relationRepository->persistAll(); // write to database immediately
    }

    /**
     * @param $uid
     * @return bool
     */
    protected function isRecordAlreadyCopied($uid)
    {
        $objectManager = $this->getObjectManager();

        /** @var \Plan2net\NewsWorkflow\Domain\Repository\RelationRepository $relationRepository */
        $relationRepository = $objectManager->get('Plan2net\NewsWorkflow\Domain\Repository\RelationRepository');

        $record = $relationRepository->findOriginalRecord($uid);

        return is_object($record) ? true : false;
    }

}
