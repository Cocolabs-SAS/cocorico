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

use Cocorico\CoreBundle\Event\ListingEvent;
use Cocorico\CoreBundle\Event\ListingEvents;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ListingParamConverter implements ParamConverterInterface
{
    /**
     * @var EntityManager
     */
    protected $em;
    protected $dispatcher;

    public function __construct(EntityManager $em, EventDispatcherInterface $dispatcher)
    {
        $this->em = $em;
        $this->dispatcher = $dispatcher;
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

        if (!isset($name) || $name != "listing") {
            return false;
        }

        if (!isset($class) || $class != "Cocorico\CoreBundle\Entity\Listing") {
            return false;
        }

        if (!isset($options['repository_method']) || $options['repository_method'] != "findOneBySlug") {
            return false;
        }

        $slug = $request->attributes->get("slug");
        if (!$slug) {
            return false;
        }

        $locale = $request->getLocale();
        if (!$locale) {
            return false;
        }

        $queryBuilder = $this->em->getRepository("CocoricoCoreBundle:Listing")->getFindOneBySlugQuery($slug, $locale);

        //Dispatch listing show query building event to eventually modify it
        $event = new ListingEvent($queryBuilder);
        $this->dispatcher->dispatch(ListingEvents::LISTING_SHOW_QUERY, $event);
        $query = $event->getQueryBuilder()->getQuery();

        try {
            $object = $query->getSingleResult();
//            $object = $this->em->getRepository("CocoricoCoreBundle:Listing")->findOneBySlug($slug, $locale);
        } catch (NoResultException $e) {
            return null;
        }

        if (null === $object) {
            throw new NotFoundHttpException(sprintf('%s object not found.', $class));
        }

        $request->attributes->set($name, $object);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return $configuration->getClass() == 'Cocorico\CoreBundle\Entity\Listing';
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
