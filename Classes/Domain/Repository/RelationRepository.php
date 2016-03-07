<?php

namespace Plan2net\NewsWorkflow\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Class RelationRepository
 *
 * @package Plan2net\NewsWorkflow\Domain\Repository
 * @author  Christina Hauk <chauk@plan2.net>
 * @author  Wolfgang Klinger <wk@plan2.net>
 */
class RelationRepository extends Repository
{

    public function initializeObject()
    {
        /** @var $querySettings \TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings */
        $querySettings = $this->objectManager->get('TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings');

        $querySettings->setIgnoreEnableFields(true); // ignore hidden and deleted
        $querySettings->setRespectStoragePage(false); // ignore storage pid

        $this->setDefaultQuerySettings($querySettings);
    }

    /**
     * @param integer $pid
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findNewRecords($pid)
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                array(
                    $query->equals('send_mail', 0),
                    $query->equals('pid_target', (integer)$pid)
                )
            )
        );

        return $query->execute();
    }

    /**
     * @param integer $pid
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findRecordsToPidTarget($pid)
    {
        $query = $this->createQuery();
        $query->matching(
            $query->equals('pid_target', (integer)$pid)
        );

        return $query->execute();
    }

    /**
     * @param integer $pid
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findRecordsToPidTargetOnlyOnce($pid)
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                array(
                    $query->equals('send_mail_changed_record', 0),
                    $query->equals('pid_target', (integer)$pid)
                )
            )
        );

        return $query->execute();
    }

    /**
     * @param integer $uid
     * @return object
     */
    public function findOriginalRecord($uid)
    {
        $query = $this->createQuery();
        $query->matching(
            $query->equals('uid_news_original', (integer)$uid)
        );

        return $query->execute()->getFirst();
    }

    public function persistAll()
    {
        $this->persistenceManager->persistAll();
    }

}
