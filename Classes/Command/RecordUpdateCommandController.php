<?php

namespace Plan2net\NewsWorkflow\Command;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class RecordUpdateCommandController
 *
 * @package Plan2net\NewsWorkflow\Command
 * @author  Christina Hauk <chauk@plan2.net>
 */
class RecordUpdateCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController {

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager
     * @inject
     */
    protected $configurationManager;

    /**
     * @var \GeorgRinger\News\Domain\Repository\NewsRepository
     * @inject
     */
    protected $newsRepository;

    /**
     * @var \Plan2net\NewsWorkflow\Domain\Repository\RelationRepository
     * @inject
     */
    protected $workflowRepository;

    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;

    /**
     * @var array
     */
    protected $settings = array();

    protected function initialize() {
        // remove constraints
        $querySettings = $this->newsRepository->createQuery()->getQuerySettings();
        $querySettings->setIgnoreEnableFields(true); // ignore hidden and deleted
        $querySettings->setRespectStoragePage(false); // ignore storage pid
        $this->newsRepository->setDefaultQuerySettings($querySettings);

        $querySettings = $this->workflowRepository->createQuery()->getQuerySettings();
        $querySettings->setIgnoreEnableFields(true); // ignore hidden and deleted
        $querySettings->setRespectStoragePage(false); // ignore storage pid
        $this->workflowRepository->setDefaultQuerySettings($querySettings);
    }

    /**
     * @param string|null $key
     * @return array|string
     */
    protected function getSettings($key = null) {
        if (empty($this->settings)) {
            $settings = $this->configurationManager->getConfiguration(
                \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
                'NewsWorkflow'
            );
            $this->settings = $settings['settings'];
        }

        if (!empty($key) && isset($this->settings[$key])) {
            if (filter_var($this->settings[$key], FILTER_VALIDATE_EMAIL)) {
                return $this->settings[$key];
            } else {
                return null;
            }
        }
        else {
            return $this->settings;
        }
    }

    /**
     * @param integer $pid
     * @param string $recipientsList
     * @param bool $notifyOnlyOnce
     */
    public function compareHashesCommand($pid, $recipientsList, $notifyOnlyOnce = true) {
        $this->initialize();
        $changedRecords = array();

        /** @var \Plan2net\NewsWorkflow\Domain\Model\Relation $records */
        $records = $this->getAllWorkflowRecords($pid, $notifyOnlyOnce);

        /** @var \Plan2net\NewsWorkflow\Domain\Model\Relation $record */
        foreach ($records as $record) {
            $newsOriginalHash = $this->getOriginalNewsRecordHash($record->getUidNewsOriginal());
            $newsHash = $record->getCompareHash();

            if (strcmp($newsOriginalHash, $newsHash) !== 0) {
                array_push($changedRecords, $record);
            }
        }

        if (!empty($changedRecords)) {
            $msg = $this->getMessage($changedRecords);
            if ($this->sendMail($recipientsList, $msg)) {
                if ($notifyOnlyOnce) {
                    foreach ($changedRecords as $record) {
                        $uid = $record->getUid();
                        $this->turnOffMessageMail($uid);
                    }
                }
            } else {
                $message = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Messaging\FlashMessage',
                    'Something went wrong by delivering the mails to all the recipients!',
                    null,
                    \TYPO3\CMS\Core\Messaging\FlashMessage::WARNING,
                    true
                );
                /** @var \TYPO3\CMS\Core\Messaging\FlashMessageService $flashMessageService */
                $flashMessageService = $this->objectManager->get('TYPO3\CMS\Core\Messaging\FlashMessageService');
                /** @var \TYPO3\CMS\Core\Messaging\FlashMessageQueue $messageQueue */
                $messageQueue = $flashMessageService->getMessageQueueByIdentifier();
                $messageQueue->enqueue($message);
            }
        }
    }

    /**
     * @param integer $uid
     * @return string
     */
    protected function getOriginalNewsRecordHash($uid) {
        $newsProps = array();
        $news = $this->newsRepository->findByUid($uid, false);

        if ($news) {
            array_push($newsProps, $news->getTitle());
            array_push($newsProps, $news->getTeaser());
            array_push($newsProps, $news->getBodytext());
        }

        $hash = hash('md5', json_encode($newsProps));

        return $hash;
    }

    /**
     * @param integer $pid
     * @param boolean $notifyOnlyOnce
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    protected function getAllWorkflowRecords($pid, $notifyOnlyOnce) {
        if ($notifyOnlyOnce) {
            $records = $this->workflowRepository->findRecordsByPidTargetOnlyOnce($pid);
        } else {
            $records = $this->workflowRepository->findRecordsByPidTarget($pid);
        }

        return $records;
    }

    /**
     * @param string $recipientsList
     * @param string $msg
     * @return bool
     */
    public function sendMail($recipientsList, $msg) {
        /** @var \TYPO3\CMS\Core\Mail\MailMessage $mail */
        $mail = $this->objectManager->get('TYPO3\CMS\Core\Mail\MailMessage');
        $subject = "Kopierte News die geändert wurden.";

        $recipients = explode(",", $recipientsList);
        $countRecipients = count($recipients);

        /** @var \TYPO3\CMS\Core\Mail\MailMessage $mail */
        $mail->setFrom($this->getSettings('emailSender'));
        $mail->setTo($recipients);
        $mail->setSubject($subject);
        $mail->setBody($msg);
        $result = $mail->send();

        return ($result == $countRecipients) ? true : false;
    }

    /**
     * @param array $changedRecords
     * @return string
     */
    protected function getMessage($changedRecords) {
        $count = count($changedRecords);
        $msg = "Folgende News haben sich geändert. Anzahl: ";
        $msg .= $count . "\r\n\r\n";

        /** @var \Plan2net\NewsWorkflow\Domain\Model\Relation $record */
        foreach ($changedRecords as $record) {
            $oID = $record->getUidNewsOriginal();
            $originalNewsRecord = $this->getDetailsForNewsRecord($oID);

            $target = $record->getPidTarget();
            $msg .= "Ordner-ID: " . $target;

            if ($originalNewsRecord) {
                $title = $originalNewsRecord->getTitle();
                $msg .= "  News mit dem Titel '" . $title . "' (ID Original News: " . $oID . ")" . "\r\n";
            }
            else {
                $copiedNewsRecord = $this->getDetailsForNewsRecord($record->getUidNews());
                if ($copiedNewsRecord) {
                    $title = $copiedNewsRecord->getTitle();
                    $msg .= "  News mit dem Titel '" . $title . "' (die Original News wurde gelöscht)" . "\r\n";
                }
                else {
                    // ignore this case, the copied news was removed
                }
            }

            $msg .= "\r\n";
        }

        return $msg;
    }

    /**
     * @param integer $uid
     * @return \GeorgRinger\News\Domain\Model\News
     */
    protected function getDetailsForNewsRecord($uid) {
        $newsRecord = $this->newsRepository->findByUid($uid, false);

        return $newsRecord;
    }

    /**
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger() {
        if ($this->logger === null) {
            $this->logger = GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')->getLogger(__CLASS__);
        }

        return $this->logger;
    }

    /**
     * @param integer $uid
     */
    protected function turnOffMessageMail($uid) {
        /** @var  \Plan2net\NewsWorkflow\Domain\Model\Relation $record */
        $record = $this->workflowRepository->findByUid($uid);
        if ($record) {
            $record->setSendMailChangedRecord(true);
        }
        $this->workflowRepository->add($record);
        $this->workflowRepository->persistAll();
    }

}
