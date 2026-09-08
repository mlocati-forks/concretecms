<?php
namespace Concrete\Core\Notification\Type;

use Concrete\Core\Entity\Notification\UserSignupNotification;
use Concrete\Core\Entity\User\UserSignup;
use Concrete\Core\Notification\Alert\Filter\StandardFilter;
use Concrete\Core\Notification\Subject\SubjectInterface;
use Concrete\Core\Notification\Subscription\StandardSubscription;

class UserSignupType extends Type
{

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Notification\Type\TypeInterface::createNotification()
     *
     * @throws \InvalidArgumentException if $signup is not a \Concrete\Core\Entity\User\UserSignup instance
     */
    public function createNotification(SubjectInterface $signup)
    {
        if (!$signup instanceof UserSignup) {
            throw new \InvalidArgumentException(t('The notification subject must be an instance of %s.', UserSignup::class));
        }

        return new UserSignupNotification($signup);
    }

    protected function createSubscription()
    {
        $subscription = new StandardSubscription('user_signup', t('User signups'));
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
        return [new StandardFilter($this, 'user_signup', t('User signups'), 'usersignupnotification')];
    }



}