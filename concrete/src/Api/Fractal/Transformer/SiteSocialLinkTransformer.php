<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Model\SocialLink as SocialLinkModel;
use Concrete\Core\Entity\Sharing\SocialNetwork\Link;
use League\Fractal\TransformerAbstract;

defined('C5_EXECUTE') or die('Access Denied.');

class SiteSocialLinkTransformer extends TransformerAbstract
{
    /**
     * @return array<string,mixed>
     */
    public function transform(Link $link): array
    {
        $service = $link->getServiceObject();

        $model = new SocialLinkModel();
        $model->id = (int) $link->getID();
        $model->service_handle = (string) $link->getServiceHandle();
        $model->service_name = $service === null ? '' : (string) $service->getDisplayName();
        $model->url = (string) $link->getURL();

        return $model->jsonSerialize();
    }
}
