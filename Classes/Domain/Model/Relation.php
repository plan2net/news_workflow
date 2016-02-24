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
     * @var int
     */
    protected $sendMail;


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
     * @return int
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
     * @return int
     */
    public function getSendMail()
    {
        return $this->sendMail;
    }

    /**
     * @param int $sendMail
     */
    public function setSendMail($sendMail)
    {
        $this->sendMail = $sendMail;
    }











}