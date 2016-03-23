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

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ListingParamConverter implements ParamConverterInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
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

        try {
            // $em = $this->getManager($options['entity_manager'], $class);
            $object = $this->em->getRepository("CocoricoCoreBundle:Listing")->findOneBySlug($slug, $locale);
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
