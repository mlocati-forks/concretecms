<?php

namespace Concrete\Core\Notification\Type;

use Concrete\Core\Entity\Notification\GroupCreateNotification;
use Concrete\Core\Entity\User\GroupCreate;
use Concrete\Core\Notification\Alert\Filter\StandardFilter;
use Concrete\Core\Notification\Subject\SubjectInterface;
use Concrete\Core\Notification\Subscription\StandardSubscription;

class GroupCreateType extends Type
{

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Notification\Type\TypeInterface::createNotification()
     *
     * @throws \InvalidArgumentException if $group is not a \Concrete\Core\Entity\User\GroupCreate instance
     */
    public function createNotification(SubjectInterface $group)
    {
        if (!$group instanceof GroupCreate) {
            throw new \InvalidArgumentException(t('The notification subject must be an instance of %s.', GroupCreate::class));
        }

        return new GroupCreateNotification($group);
    }

    protected function createSubscription()
    {
        $subscription = new StandardSubscription('group_create', t('Group creations'));
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
            new StandardFilter($this, 'group_create', t('Group creations'),
                'groupcreatenotification')
        ];
    }

}