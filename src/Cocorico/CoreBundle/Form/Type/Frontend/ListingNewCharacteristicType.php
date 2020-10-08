<?php
namespace Cocorico\CoreBundle\Form\Type\Frontend;

use Cocorico\CoreBundle\Entity\Listing;
use Cocorico\CoreBundle\Form\Type\ListingListingCharacteristicType;
use Cocorico\CoreBundle\Form\Type\ListingCharacteristicType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\RequestStack;
use Cocorico\CoreBundle\Model\Manager\ListingManager;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * ListingNewCharacteristicType
 */
class ListingNewCharacteristicType  extends AbstractType
{
    protected $request;
    protected $locale;
    protected $locales;
    protected $lem;

    /**
     * @param RequestStack   $requestStack
     * @param array          $locales
     * @param ListingManager $lem
     */
    public function __construct(
        RequestStack $requestStack,
        $locales,
        ListingManager $lem
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->locale = $this->request->getLocale();
        $this->locales = $locales;
        $this->lem = $lem;
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add('listingListingCharacteristicsOrderedByGroup',
            //    ListingCharacteristicType::class,
            //    array (
            //        'mapped' => false
            //    )
            //);
            ->add(
                'listingListingCharacteristicsOrderedByGroup',
                CollectionType::class,
                array(
                    'entry_type' => ListingListingCharacteristicType::class,
                    # 'entry_options' => [
                    #     'multiple' => True
                    # ],
                    /** @Ignore */
                    'label' => false
                )
            );

        //Add new ListingCharacteristics eventually not already attached to listing
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                /** @var Listing $listing */
                $listing = $event->getData();
                $listing = $this->lem->refreshListingListingCharacteristics($listing);
                $event->setData($listing);
            }
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
                'allow_extra_fields' => true,
                'data_class' => 'Cocorico\CoreBundle\Entity\Listing',
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
        return 'listing_new_characteristic';
    }
}
