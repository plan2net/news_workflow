<?php

namespace Plan2net\NewsWorkflow\Domain\Model;

/**
 * Class Relation
 * @package Plan2net\NewsWorkflow\Domain\Model
 * @author Christina Hauk <chauk@plan2.net>
 */
class Mail extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{
    /**
     * @var string
     */
    protected $mailTo;

    /**
     * @var string
     */
    protected $mailFrom;

    /**
     * @var string
     */
    protected $subject;

    /**
     * @var string
     */
    protected $message;

    /**
     * @return string
     */
    public function getMailTo()
    {
        return $this->mailTo;
    }

    /**
     * @param string $mailTo
     */
    public function setMailTo($mailTo)
    {
        $this->mailTo = $mailTo;
    }

    /**
     * @return string
     */
    public function getMailFrom()
    {
        return $this->mailFrom;
    }

    /**
     * @param string $mailFrom
     */
    public function setMailFrom($mailFrom)
    {
        $this->mailFrom = $mailFrom;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }


}