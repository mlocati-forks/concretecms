<?php
namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\TokenScopes;
use Concrete\Core\Permission\Checker;
use Concrete\Core\User\Group\Group;
use League\Fractal\TransformerAbstract;

class GroupTransformer extends TransformerAbstract
{

    public function transform(Group $group)
    {
        if (!$this->isVisible($group)) {
            return ['id' => $group->getGroupID()];
        }

        $data['id'] = $group->getGroupID();
        $data['name'] = $group->getGroupName();
        return $data;
    }

    /**
     * Whether the request may see the whole of a group of users: the scopes of its token settle it
     * first, and then the permissions, the ones that the endpoint serving these things asks about.
     */
    protected function isVisible(Group $group): bool
    {
        return app(TokenScopes::class)->has('groups:read')
            && (bool) (new Checker($group))->canViewTreeNode();
    }
}
