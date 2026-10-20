<?php

declare(strict_types=1);

namespace Concrete\Core\Api\OpenApi\Parameter;

use Concrete\Core\Api\OpenApi\SpecParameter;
use Concrete\Core\Api\OpenApi\SpecSchema;

defined('C5_EXECUTE') or die('Access Denied.');

class SiteParameter extends SpecParameter
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\OpenApi\SpecParameter::getName()
     */
    public function getName(): string
    {
        return 'site';
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\OpenApi\SpecParameter::getIn()
     */
    public function getIn(): string
    {
        return 'query';
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\OpenApi\SpecParameter::getDescription()
     */
    public function getDescription(): string
    {
        return t('ID of the site whose entries this request works with, which GET /sites lists. It holds for an entity that keeps its entries apart by site: left out, a list answers with the entries of every site, while a new entry joins the ones of the site the domain of this request answers for. An entity that shares its entries answers the same whatever it says.');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\OpenApi\SpecParameter::getSchema()
     */
    public function getSchema(): ?SpecSchema
    {
        return new SpecSchema('integer', 'int64');
    }
}
