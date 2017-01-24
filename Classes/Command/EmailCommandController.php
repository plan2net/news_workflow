<?php

namespace Plan2net\NewsWorkflow\Command;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class EmailCommandController
 *
 * @package Plan2net\NewsWorkflow\Command
 * @author  Christina Hauk <chauk@plan2.net>
 */
class EmailCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController {

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
     * @param integer $pid
     * @param string $recipients
     */
    public function sendMailCommand($pid, $recipientsList) {
        $this->initialize();

        $records = $this->workflowRepository->findNewRecordsByPid($pid);

        /** @var \TYPO3\CMS\Core\Mail\MailMessage $mail */
        $mail = $this->objectManager->get('TYPO3\CMS\Core\Mail\MailMessage');
        $subject = 'Neu kopierte News';

        $recipients = explode(',', $recipientsList);
        $countRecipients = count($recipients);

        if ($records) {
            $message = $this->getMessage($records);

            /** @var \TYPO3\CMS\Core\Mail\MailMessage $mail */
            $mail->setFrom($this->getSettings('emailSender'));
            $mail->setTo($recipients);
            $mail->setSubject($subject);
            $mail->setBody($message);
            $result = $mail->send();

            if ($result == $countRecipients) {
                $this->setSendMailValue($records);
            } else {
                $this->getLogger()->error('We are sorry! Something went wrong by delivering the email.');
            }
        }
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

        if (!empty($key)) {
            if (isset($this->settings[$key])) {
                return $this->settings[$key];
            }
            return '';
        }
        else {
            return $this->settings;
        }
    }

    /**
     * @param \TYPO3\CMS\Extbase\Persistence\QueryResultInterface $records
     * @return string
     */
    protected function getMessage($records) {
        $message = "Es wurden neue News kopiert! Anzahl: " . $records->count() . "\n";

        /** @var \Plan2net\NewsWorkflow\Domain\Model\Relation $record */
        foreach ($records as $record) {
            $oID = $record->getUidNewsOriginal();
            $targetPid = $record->getPidTarget();
            $backendUser = $record->getReleasePerson();

            $mailAddress = $backendUser->getEmail();
            $releasedPersonName = $backendUser->getUserName();

            $originalNews = $this->newsRepository->findByUid($oID, false); // disable respectEnableFields
            $title = $originalNews->getTitle();

            if (empty($mailAddress)) {
                $mailAddress = 'Keine E-Mail Addresse verfügbar';
            }

            if (empty($releasedPersonName)) {
                $releasedPersonName = 'Kein Name angegeben';
            }

            $message = $message . "\n Ordner-ID: " . $targetPid;
            $message = $message . "\n News mit dem Titel '" . $title . "' (ID Original News: " . $oID . ")";
            $message = $message . "\n Person, die die News veröffentlicht hat: " . $releasedPersonName . " (" . $mailAddress . ", ID: " . $backendUser->getUid() . ")";
            $message = $message . "\n\n";
        }

        return $message;
    }

    /**
     * @param array $records
     */
    protected function setSendMailValue($records) {
        /** @var \Plan2net\NewsWorkflow\Domain\Repository\RelationRepository $workflowRepository */
        $workflowRepository = $this->objectManager->get('Plan2net\NewsWorkflow\Domain\Repository\RelationRepository');

        /** @var \Plan2net\NewsWorkflow\Domain\Model\Relation $record */
        foreach ($records as $record) {
            $record->setSendMail(true);
            $workflowRepository->update($record);
        }

        $workflowRepository->persistAll();
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

}
