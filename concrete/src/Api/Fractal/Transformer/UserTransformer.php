<?php
namespace Concrete\Core\Api\Fractal\Transformer;

use Carbon\Carbon;
use Concrete\Core\Permission\Checker;
use Concrete\Core\User\User;
use Concrete\Core\User\UserInfo;
use Concrete\Core\Api\Resources;
use Concrete\Core\Api\TokenScopes;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{

    protected $availableIncludes = [
        'groups',
        'custom_attributes',
    ];

    /**
     * Basic transforming of a user into an array
     *
     * @param UserInfo $user
     * @return array
     */
    public function transform(UserInfo $user)
    {
        if (!$this->isVisible($user)) {
            return ['id' => $user->getUserID()];
        }

        return [
            'id' => $user->getUserID(),
            'username' => $user->getUserName(),
            'email' => $user->getUserEmail(),
            'date_added' => Carbon::make($user->getUserDateAdded())->toAtomString(),
            'status' => $this->getUserStatus($user),
            'total_logins' => $user->getNumLogins(),
            'date_password_last_changed' => $user->getUserLastPasswordChange() ?
                Carbon::make($user->getUserLastPasswordChange())->toAtomString() : null,
            'date_last_updated' => $user->getUserDateLastUpdated() ?
                Carbon::make($user->getUserDateLastUpdated())->toAtomString() : null,
            'has_avatar' => $user->hasAvatar(),
            'avatar' => $user->getUserAvatar()->getPath(),
            'last_login' => $user->getLastLogin(),
            'previous_login' => $user->getPreviousLogin(),
            'timezone' => $user->getUserTimezone(),
            'language' => $user->getUserDefaultLanguage(),
        ];
    }

    public function includeGroups(UserInfo $user)
    {
        $groups = $user->getUserObject()->getUserGroupObjects();
        return new Collection($groups, new GroupTransformer(), Resources::RESOURCE_GROUPS);
    }

    public function includeCustomAttributes(UserInfo $user)
    {
        $values = $user->getObjectAttributeCategory()->getAttributeValues($user);
        return new Collection($values, new AttributeValueTransformer(), Resources::RESOURCE_CUSTOM_ATTRIBUTES);
    }
    protected function getUserStatus(UserInfo $user)
    {
        if ($user->isActive()) {
            $currentStatus = 'active';
        } elseif ($user->isValidated()) {
            $currentStatus = 'inactive';
        } else {
            $currentStatus = 'unvalidated';
        }
        return $currentStatus;
    }

    /**
     * Whether the request may see the whole of a user: the scopes of its token settle
     * it first, and then the permissions, the ones the endpoint serving these things asks about.
     */
    protected function isVisible(UserInfo $user): bool
    {
        // the user the token belongs to reads their own account, whatever its scopes say: that is
        // what GET /account hands over, with account:read alone
        if ($this->isTheUserOfTheToken($user)) {
            return true;
        }

        return app(TokenScopes::class)->has('users:read')
            && (bool) (new Checker($user))->canViewUser();
    }

    /**
     * Whether this is the user that the token belongs to, who reads their own account whatever the
     * scopes of the token say: that is what GET /account hands over, with account:read alone.
     */
    private function isTheUserOfTheToken(UserInfo $user): bool
    {
        $userID = app(User::class)->getUserID();

        return $userID && (int) $userID === (int) $user->getUserID();
    }
}
