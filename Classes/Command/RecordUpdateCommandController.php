<?php

namespace Plan2net\NewsWorkflow\Command;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class RecordUpdateCommandController
 * @package Plan2net\NewsWorkflow\Command
 * @author Christina Hauk <chauk@plan2.net>
 */
class RecordUpdateCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController {

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager
     */
    protected $objectManager;

    /** @var \GeorgRinger\News\Domain\Repository\NewsRepository $newsRepository */
    protected $newsRepository;

    /** @var \Plan2net\NewsWorkflow\Domain\Repository\RelationRepository $workflowRepository */
    protected $workflowRepository;


    /**
     * RecordUpdateCommandController constructor.
     */
    public function __construct()
    {
        if ($this->objectManager === null) {
            $this->objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
        }

        $this->newsRepository = $this->objectManager->get('GeorgRinger\News\Domain\Repository\NewsRepository');
        $this->workflowRepository = $this->objectManager->get('Plan2net\NewsWorkflow\Domain\Repository\RelationRepository');

        //return $this->objectManager;
    }

    /**
     * @var \TYPO3\CMS\Core\Log\Logger
     */
    protected $logger;

    /**
     * @param $pid
     * @param $recipientsList
     * @param bool $notifyOnlyOnce
     */
    public function compareHashesCommand ($pid, $recipientsList, $notifyOnlyOnce = true) {

        $changedRecords = array();
        $result = false;


        /** @var \Plan2net\NewsWorkflow\Domain\Model\Relation $records */
        $records = $this->getAllWorkflowRecords($pid, $notifyOnlyOnce);

        /** @var \Plan2net\NewsWorkflow\Domain\Model\Relation $record */
        foreach($records as $record) {
            $newsOriginalHash = $this->getOriginalNewsRecordHash($record->getUidNewsOriginal());
            $newsHash = $record->getCompareHash();

            if(strcmp($newsOriginalHash,$newsHash) !== 0) {
                array_push($changedRecords, $record);
            }
        }

        if(!empty($changedRecords)) {
            $msg = $this->getMessage($changedRecords);
            $result = $this->sendMail($recipientsList, $msg);
        }

       if($result) {
            if($notifyOnlyOnce) {
                foreach ($changedRecords as $record) {
                    $uid = $record->getUid();
                    $this->turnOffMessageMail($uid);
                }
            }
        } else {
            print("Something went wrong by delivering the mails to the recipients! Please send the mails again");
        }

    }

    /**
     * @param $uid
     * @return string
     */
    protected function getOriginalNewsRecordHash($uid) {

        $newsProps = array();

        // get query settings and remove all constraints (to get ALL records)
        $querySettings = $this->newsRepository->createQuery()->getQuerySettings();
        $querySettings->setIgnoreEnableFields(true); // ignore hidden and deleted
        $querySettings->setRespectStoragePage(false); // ignore storage pid
        $this->newsRepository->setDefaultQuerySettings($querySettings);

        $news = $this->newsRepository->findByUid($uid, false);

        if($news) {
            array_push($newsProps, $news->getTitle());
            array_push($newsProps, $news->getTeaser());
            array_push($newsProps, $news->getBodytext());
        }

        $hash = hash('md5', json_encode($newsProps));
        return $hash;

    }

    /**
     * @param $pid
     * @param $notifyOnlyOnce
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    protected function getAllWorkflowRecords ($pid, $notifyOnlyOnce) {


        // get query settings and remove all constraints (to get ALL records)
        $querySettings = $this->workflowRepository->createQuery()->getQuerySettings();
        $querySettings->setIgnoreEnableFields(true); // ignore hidden and deleted
        $querySettings->setRespectStoragePage(false); // ignore storage pid
        $this->workflowRepository->setDefaultQuerySettings($querySettings);

        if($notifyOnlyOnce) {
            $records = $this->workflowRepository->findRecordsToPidTargetOnlyOnce($pid);
        } else {
            $records = $this->workflowRepository->findRecordsToPidTarget($pid);
        }

        return $records;
    }

    public function sendMail ($recipientsList, $msg)  {

        /** @var \TYPO3\CMS\Core\Mail\MailMessage $mail */
        $mail = $this->objectManager->get('TYPO3\CMS\Core\Mail\MailMessage');
        $subject = "Kopierte News die geändert wurden.";

        $recipients = explode(",", $recipientsList);
        $countRecipients = count($recipients);


            /** @var \TYPO3\CMS\Core\Mail\MailMessage $mail */
            $mail->setFrom("no-replay@vu-wien.ac.at");
            $mail->setTo($recipients);
            $mail->setSubject($subject);
            $mail->setBody($msg);
            $result = $mail->send();


            if ($result == $countRecipients) {
                return true;

            } else {
                $this->getLogger()->error("We are sorry!Something went wrong by delivering the email.");
            }
    }

    protected function getMessage($changedRecords) {

        $count = count($changedRecords);
        $msg = "Folgende News haben sich geändert. Anzahl: ";
        $msg = $msg . $count . "\n";

        foreach($changedRecords as $record) {
            $oID = $record->getUidNewsOriginal();

            $title = $this->getDetailsToRecord($oID)->getTitle();
            $target = $record->getPidTarget();

            $msg = $msg . "\n Ordner-ID: " . $target;
            $msg = $msg . "\n News mit dem Titel '" . $title . "'[ID Original News: " . $oID . "]";
            $msg = $msg . "\n\n";
        }

        return $msg;
    }

    /**
     * @param $uid
     * @return \GeorgRinger\News\Domain\Model\News
     */
    protected function getDetailsToRecord($uid)
    {

        $querySettings = $this->newsRepository->createQuery()->getQuerySettings();
        $querySettings->setIgnoreEnableFields(true); // ignore hidden and deleted
        $querySettings->setRespectStoragePage(false); // ignore storage pid
        $this->newsRepository->setDefaultQuerySettings($querySettings);
        $newsRecord = $this->newsRepository->findByUid($uid, false);

        return $newsRecord;
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
     * @param $uid
     */
    protected function turnOffMessageMail($uid) {

        // get query settings and remove all constraints (to get ALL records)
        $querySettings = $this->workflowRepository->createQuery()->getQuerySettings();
        $querySettings->setIgnoreEnableFields(true); // ignore hidden and deleted
        $querySettings->setRespectStoragePage(false); // ignore storage pid
        $this->workflowRepository->setDefaultQuerySettings($querySettings);

        /** @var  \Plan2net\NewsWorkflow\Domain\Model\Relation  $record */
        $record = $this->workflowRepository->findByUid($uid);
        if ($record) {
            $record->setSendMailChangedRecord(true);
        }
    }


}