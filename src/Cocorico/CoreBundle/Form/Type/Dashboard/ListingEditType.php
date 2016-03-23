<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Form\Type\Dashboard;

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Model\Manager\ListingManager;
use Cocorico\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContext;

class ListingEditType extends AbstractType
{
    protected $securityContext;
    protected $request;
    protected $locale;
    protected $locales;
    protected $lem;
    protected $timeUnit;
    protected $timeUnitIsDay;

    /**
     * @param SecurityContext $securityContext
     * @param RequestStack    $requestStack
     * @param array           $locales
     * @param ListingManager  $lem
     * @param int             $timeUnit
     */
    public function __construct(
        SecurityContext $securityContext,
        RequestStack $requestStack,
        $locales,
        ListingManager $lem,
        $timeUnit
    ) {
        $this->securityContext = $securityContext;
        $this->request = $requestStack->getCurrentRequest();
        $this->locale = $this->request->getLocale();
        $this->locales = $locales;
        $this->lem = $lem;
        $this->timeUnit = $timeUnit;
        $this->timeUnitIsDay = ($timeUnit % 1440 == 0) ? true : false;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'user',
                'entity_hidden',
                array(
                    'data' => $this->securityContext->getToken()->getUser(),
                    'class' => 'Cocorico\UserBundle\Entity\User',
                    'data_class' => null
                )
            );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Cocorico\CoreBundle\Entity\Listing',
                'intention' => 'listing_edit',
                'translation_domain' => 'cocorico_listing',
                'cascade_validation' => true,
                //'validation_groups' => array('Listing'),
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'listing_edit';
    }

}
