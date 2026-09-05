<?php
namespace Concrete\Core\Permission\Registry\Entry\Access\Entity;

use Concrete\Core\Permission\Access\Entity\Entity as AccessEntity;
use Concrete\Core\Permission\Registry\Entry\EntrySubjectInterface;

interface EntityInterface extends EntrySubjectInterface
{

    /**
     * @return \Concrete\Core\Permission\Access\Entity\Entity|false|null NULL or false if the entity can't be resolved
     */
    function getAccessEntity();
}

