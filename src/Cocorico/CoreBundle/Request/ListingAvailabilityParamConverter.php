<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Request;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\NoResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ListingAvailabilityParamConverter implements ParamConverterInterface
{
    /**
     * @var DocumentManager
     */
    protected $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException       When unable to guess how to get a Doctrine instance from the request information
     * @throws NotFoundHttpException When object not found
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $name = $configuration->getName();
        $class = $configuration->getClass();
        $options = $this->getOptions($configuration);

        if (!isset($name) || $name != "listing_availability") {
            return false;
        }

        if (!isset($class) || $class != "Cocorico\CoreBundle\Document\ListingAvailability") {
            return false;
        }

        $id = $request->attributes->get("id");
        if (!$id) {
            return false;
        }

        try {
            $object = $this->dm->find("Cocorico\CoreBundle\Document\ListingAvailability", $id);

        } catch (NoResultException $e) {
            return null;
        }

        if (null === $object) {
            throw new NotFoundHttpException(sprintf('%s object not found.', $class));
        }

        $listingId = $request->attributes->get("listing_id");
        if ($listingId != $object->getListingId()) {
            throw new NotFoundHttpException(sprintf('%s object wrong listing.', $class));
        }

        $request->attributes->set($name, $object);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return $configuration->getClass() == 'Cocorico\CoreBundle\Document\ListingAvailability';
    }

    protected function getOptions(ParamConverter $configuration)
    {
        return array_replace(
            array(
                'entity_manager' => null,
                'exclude' => array(),
                'mapping' => array(),
                'strip_null' => false,
            ),
            $configuration->getOptions()
        );
    }

}
