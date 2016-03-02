<?php

namespace Plan2net\NewsWorkflow\Domain\Model;

/**
 * Class Relation
 * @package Plan2net\NewsWorkflow\Domain\Model
 * @author Christina Hauk <chauk@plan2.net>
 */
class Relation extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {
    /**
     * @var int
     */
    protected $uidNews;

    /**
     * @var int
     */
    protected $uidNewsOriginal;

    /**
     * @var int
     */
    protected $pidTarget;

    /**
     * @var integer
     */
    protected $dateCreated;

    /**
     * @var bool
     */
    protected $sendMail;

    /**
     * @var \TYPO3\CMS\Beuser\Domain\Model\BackendUser
     */
    protected $releasePerson;


    /**
     * @var string
     */
    protected $compareHash;

    /**
     * @var bool
     */
    protected $sendMailChangedRecord = false;



    /**
     * @return int
     */
    public function getUidNews()
    {
        return $this->uidNews;
    }

    /**
     * @param int $uidNews
     */
    public function setUidNews($uidNews)
    {
        $this->uidNews = $uidNews;
    }

    /**
     * @return int
     */
    public function getUidNewsOriginal()
    {
        return $this->uidNewsOriginal;
    }

    /**
     * @param int $uidNewsOriginal
     */
    public function setUidNewsOriginal($uidNewsOriginal)
    {
        $this->uidNewsOriginal = $uidNewsOriginal;
    }

    /**
     * @return integer
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param int $dateCreated
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;
    }

    /**
     * @return int
     */
    public function getPidTarget()
    {
        return $this->pidTarget;
    }

    /**
     * @param int $pidTarget
     */
    public function setPidTarget($pidTarget)
    {
        $this->pidTarget = $pidTarget;
    }

    /**
     * @return boolean
     */
    public function isSendMail()
    {
        return $this->sendMail;
    }

    /**
     * @param boolean $sendMail
     */
    public function setSendMail($sendMail)
    {
        $this->sendMail = $sendMail;
    }


    /**
     * @return \TYPO3\CMS\Beuser\Domain\Model\BackendUser
     */
    public function getReleasePerson()
    {
        return $this->releasePerson;
    }

    /**
     * @param \TYPO3\CMS\Beuser\Domain\Model\BackendUser $releasePerson
     */
    public function setReleasePerson($releasePerson)
    {
        $this->releasePerson = $releasePerson;
    }



    /**
     * @return string
     */
    public function getCompareHash()
    {
        return $this->compareHash;
    }

    /**
     * @param string $compareHash
     */
    public function setCompareHash($compareHash)
    {
        $this->compareHash = $compareHash;
    }

    /**
     * @return boolean
     */
    public function isSendMailChangedRecord()
    {
        return $this->sendMailChangedRecord;
    }

    /**
     * @param boolean $sendMailChangedRecord
     */
    public function setSendMailChangedRecord($sendMailChangedRecord)
    {
        $this->sendMailChangedRecord = $sendMailChangedRecord;
    }


}