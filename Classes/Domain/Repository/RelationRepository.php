<?php

namespace Plan2net\NewsWorkflow\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Class RelationRepository
 * @package Plan2net\NewsWorkflow\Domain\Repository
 * @author Christina Hauk <chauk@plan2.net>
 */
class RelationRepository extends Repository
{

    public function persistAll()
    {
        $this->persistenceManager->persistAll();
    }

    public function findNewRecords ($pid) {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd([$query->like('send_mail', 0), $query->like('pid_target', $pid)])
        );
        return $query->execute();
    }

}