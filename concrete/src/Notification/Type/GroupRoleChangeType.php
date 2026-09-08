<?php

namespace Concrete\Core\Notification\Type;

use Concrete\Core\Entity\Notification\GroupRoleChangeNotification;
use Concrete\Core\Entity\User\GroupRoleChange;
use Concrete\Core\Notification\Alert\Filter\StandardFilter;
use Concrete\Core\Notification\Subject\SubjectInterface;
use Concrete\Core\Notification\Subscription\StandardSubscription;

class GroupRoleChangeType extends Type
{

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Notification\Type\TypeInterface::createNotification()
     *
     * @throws \InvalidArgumentException if $group is not a \Concrete\Core\Entity\User\GroupRoleChange instance
     */
    public function createNotification(SubjectInterface $group)
    {
        if (!$group instanceof GroupRoleChange) {
            throw new \InvalidArgumentException(t('The notification subject must be an instance of %s.', GroupRoleChange::class));
        }

        return new GroupRoleChangeNotification($group);
    }

    protected function createSubscription()
    {
        $subscription = new StandardSubscription('group_role_change', t('Group role changes'));
        return $subscription;
    }

    public function getSubscription(SubjectInterface $subject)
    {
        return $this->createSubscription();
    }

    public function getAvailableSubscriptions()
    {
        return array($this->createSubscription());
    }


    public function getAvailableFilters()
    {
        return [
            new StandardFilter($this, 'group_role_change', t('Group role changes'),
                'grouprolechangenotification')
        ];
    }

}