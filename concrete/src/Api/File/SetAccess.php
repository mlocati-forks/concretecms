<?php

declare(strict_types=1);

namespace Concrete\Core\Api\File;

use Concrete\Core\File\Set\Set;
use Concrete\Core\File\Set\SetList;
use Concrete\Core\User\User;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The file sets a request may use: the ones of the site, along with the ones of the user it carries.
 */
class SetAccess
{
    /**
     * @var \Concrete\Core\User\User
     */
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the sets the request may use, by name: a request carrying no user, as a token of the client
     * credentials flow does, sees the sets of the site alone. The saved searches are left out of both
     * lists: they are no set of files, and the file manager keeps them in the same table.
     *
     * @return \Concrete\Core\File\Set\Set[]
     */
    public function getSets(): array
    {
        if ($this->user->isRegistered()) {
            return Set::getMySets($this->user);
        }
        $list = new SetList();
        $list->filterByType(Set::TYPE_PUBLIC);

        return $list->get();
    }

    /**
     * Get the set of an ID, where the request may use it.
     *
     * @param int|string $setID
     *
     * @return \Concrete\Core\File\Set\Set|null NULL where the request has no set of that ID
     */
    public function getSet($setID): ?Set
    {
        $wanted = (int) $setID;
        if ($wanted === 0) {
            return null;
        }
        foreach ($this->getSets() as $set) {
            if ((int) $set->getFileSetID() === $wanted) {
                return $set;
            }
        }

        return null;
    }
}
