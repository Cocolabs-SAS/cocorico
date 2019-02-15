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

use Cocorico\CoreBundle\Form\Type\EntityHiddenType;
use Cocorico\CoreBundle\Model\Manager\ListingManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Validator\Constraints\Valid;

class ListingEditType extends AbstractType
{
    protected $securityTokenStorage;
    protected $request;
    protected $locale;
    protected $locales;
    protected $lem;
    protected $timeUnit;
    protected $timeUnitIsDay;

    /**
     * @param TokenStorage   $securityTokenStorage
     * @param RequestStack   $requestStack
     * @param array          $locales
     * @param ListingManager $lem
     * @param int            $timeUnit
     */
    public function __construct(
        TokenStorage $securityTokenStorage,
        RequestStack $requestStack,
        $locales,
        ListingManager $lem,
        $timeUnit
    ) {
        $this->securityTokenStorage = $securityTokenStorage;
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
                EntityHiddenType::class,
                array(
                    'data' => $this->securityTokenStorage->getToken()->getUser(),
                    'class' => 'Cocorico\UserBundle\Entity\User',
                    'data_class' => null
                )
            );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(
            array(
                'data_class' => 'Cocorico\CoreBundle\Entity\Listing',
                'csrf_token_id' => 'listing_edit',
                'translation_domain' => 'cocorico_listing',
                'constraints' => new Valid(),
                //'validation_groups' => array('Listing'),
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'listing_edit';
    }
}
