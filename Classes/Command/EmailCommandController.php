<?php

namespace Plan2net\NewsWorkflow\Command;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class EmailCommandController
 * @package Plan2net\NewsWorkflow\Command
 * @author Christina Hauk <chauk@plan2.net>
 */
class EmailCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController
{


    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager
     */
    protected $objectManager;

    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;

    public function __construct()
    {
        if ($this->objectManager === null) {
            $this->objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        }

        return $this->objectManager;
    }

    /**
     * @param int $pid
     * @param string $recipients
     */
    public function sendMailCommand($pid, $recipientsList) {

        /** @var TYPO3\CMS\Core\Mail\MailMessage $mail */
        $mail = $this->objectManager->get('TYPO3\CMS\Core\Mail\MailMessage');
        $records = $this->getWorkflowRecords($pid);
        $subject = "Neu kopierte News";


        $recipients = explode(",", $recipientsList);
        $countRecipients = count($recipients);

        if(is_array($records)) {

            $msg = $this->getMessage($records);

            /** @var TYPO3\CMS\Core\Mail\MailMessage $mail */
            $mail->setFrom("no-replay@vu-wien.ac.at");
            $mail->setTo($recipients);
            $mail->setSubject($subject);
            $mail->setBody($msg);
            $result = $mail->send();

            /*if($result == $countRecipients) {
                $this->setSendMailValue($records);
            } else {
                $this->getLogger()->error("We are sorry!Something went wrong by delivering the email.");
            }*/

        } else {

            $this->getLogger()->error("Today are no new records available!");
        }

    }

    /**
     * @param $pid
     * @return array|bool
     */
    protected function getWorkflowRecords ($pid) {

        $records = array();

        /** @var \Plan2net\NewsWorkflow\Domain\Repository\RelationRepository $workflowRepository */
        $workflowRepository = $this->objectManager->get('Plan2net\NewsWorkflow\Domain\Repository\RelationRepository');

        // get query settings and remove all constraints (to get ALL records)
        $querySettings = $workflowRepository->createQuery()->getQuerySettings();
        $querySettings->setIgnoreEnableFields(true); // ignore hidden and deleted
        $querySettings->setRespectStoragePage(false); // ignore storage pid
        $workflowRepository->setDefaultQuerySettings($querySettings);

        $workflowRecords= $workflowRepository->findNewRecords($pid);

        if($workflowRecords->count() > 0) {
            foreach ($workflowRecords as $record) {
                array_push($records, $record);
            }
            return $records;
        } else {
            return false;
        }

    }

    protected function getMessage($records) {
        $count = count($records);
        $msg = "Es wurden neue News kopiert! Anzahl: " . $count . ".\n";

        foreach($records as $record) {

            $oID = $record->getUidNewsOriginal();
            $ID = $record->getUidNews();
            $author = $this->getDetailsToRecord($oID)->getAuthor();
            $title = $this->getDetailsToRecord($oID)->getTitle();
            $target = $record->getPidTarget();

            if(empty($author)) {
                $author = "kein Author verfügbar";
            }

            $msg = $msg .  "Ordner-ID: " . $target;
            $msg = $msg .  "\n News Record mit dem Titel '".$title ."'[ID Original News: " . $oID . "]";
            $msg = $msg .  "\n Author: " . $author;
            $msg = $msg .  "\n\n";
        }
        return $msg;
    }

    /**
     * @param $uid
     * @return \GeorgRinger\News\Domain\Model\News
     */
    protected function getDetailsToRecord($uid) {

        /** @var \GeorgRinger\News\Domain\Repository\NewsRepository $newsRepository */
        $newsRepository = $this->objectManager->get('GeorgRinger\News\Domain\Repository\NewsRepository');

        $querySettings = $newsRepository->createQuery()->getQuerySettings();
        $querySettings->setIgnoreEnableFields(true); // ignore hidden and deleted
        $querySettings->setRespectStoragePage(false); // ignore storage pid
        $newsRepository->setDefaultQuerySettings($querySettings);
        $newsRecord = $newsRepository->findByUid($uid, false);

        return $newsRecord;
    }

    protected function setSendMailValue ($records) {

        /** @var \Plan2net\NewsWorkflow\Domain\Repository\RelationRepository $workflowRepository */
        $workflowRepository = $this->objectManager->get('Plan2net\NewsWorkflow\Domain\Repository\RelationRepository');

        foreach($records as $record) {
            $record->setSendMail = 1;
            $workflowRepository->add($record);
        }

        $workflowRepository->persistAll();
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


}