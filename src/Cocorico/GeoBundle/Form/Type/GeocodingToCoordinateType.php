<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\GeoBundle\Form\Type;

use Cocorico\GeoBundle\Form\DataTransformer\GeocodingToCoordinateEntityTransformer;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GeocodingToCoordinateType extends AbstractType
{
    /**
     * @var ObjectManager
     */
    private $om;
    /**
     * @var array
     */
    private $locales;
    private $request;
    private $locale;
    private $googlePlaceAPIKey;

    /**
     * @param ObjectManager $om
     * @param array         $locales
     * @param RequestStack  $requestStack
     * @param string        $googlePlaceAPIKey
     */
    public function __construct(ObjectManager $om, $locales, RequestStack $requestStack, $googlePlaceAPIKey = null)
    {
        $this->om = $om;
        $this->locales = $locales;
        $this->request = $requestStack->getCurrentRequest();
        $this->locale = $this->request->getLocale();
        $this->googlePlaceAPIKey = $googlePlaceAPIKey ? $googlePlaceAPIKey : null;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new GeocodingToCoordinateEntityTransformer(
            $this->om,
            $this->locales,
            $this->locale,
            $this->googlePlaceAPIKey
        );
        $builder->addModelTransformer($transformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'invalid_message' => 'geo.location.address_invalid',
                'error_bubbling' => false,
                'compound' => false,

            )
        );
        parent::configureOptions($resolver);
    }

    public function getParent()
    {
        return 'hidden';
    }

    /**
     * BC
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'geocoding_to_coordinate';
    }
}
