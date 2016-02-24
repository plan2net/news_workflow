<?php

namespace Plan2net\NewsWorkflow\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;

class RelationRepository extends Repository {

    public function persistAll() {
        $this->persistenceManager->persistAll();
    }

}