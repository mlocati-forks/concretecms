<?php
namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\TokenScopes;
use Concrete\Core\Entity\Attribute\Value\Value\SelectValueOption;
use League\Fractal\TransformerAbstract;

class OptionListOptionTransformer extends TransformerAbstract
{

    public function transform(SelectValueOption $option)
    {
        if (!$this->isVisible($option)) {
            return ['id' => $option->getSelectAttributeOptionID()];
        }

        $data['id'] = $option->getSelectAttributeOptionID();
        $data['value'] = $option->getSelectAttributeOptionValue();
        $data['display_value'] = $option->getSelectAttributeOptionDisplayValue('string');
        return $data;
    }

    /**
     * Whether the request may see the whole of an option: the scopes of its token settle it, and the
     * endpoint handing the keys over asks about no permission of its own.
     */
    protected function isVisible(SelectValueOption $option): bool
    {
        return app(TokenScopes::class)->has('definitions:read');
    }
}
