<?php
namespace Cocorico\CoreBundle\Form\Type\Frontend;

use Cocorico\CoreBundle\Entity\Directory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Cocorico\CoreBundle\Form\Type\ListingCategoryType;
use Cocorico\CoreBundle\Model\DirectorySearchRequest;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DirectoryFilterType extends AbstractType
{
     /**
     * @param EntityManager            $entityManager
     * @param RequestStack             $requestStack
     * @param EventDispatcherInterface $dispatcher
     * @param array                    $parameters
     */
    public function __construct(
        EntityManager $entityManager,
        RequestStack $requestStack,
        EventDispatcherInterface $dispatcher,
        $parameters
    ) {
        $this->entityManager = $entityManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->dispatcher = $dispatcher;

        $parameters = $parameters["parameters"];
    }


    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var DirectorySearchRequest $directorySearchRequest */
        $directorySearchRequest = $builder->getData();

        //CATEGORIES
        /** @var ListingCategoryRepository $categoryRepository */
        $categoryRepository = $this->entityManager->getRepository("CocoricoCoreBundle:ListingCategory");
        $categories = $categoryRepository->findCategoriesByIds(
            //FIXME: Make this work
            // $directorySearchRequest->getCategories(),
            [],
            'fr'
        );


        $builder
            ->add('format',
                ChoiceType::class,
                array(
                    'mapped' => false,
                    'expanded' => false,
                    'empty_data' => 'xlsx',
                    'choices' => [
                        'Excel' => 'xlsx',
                        'Open Office' => 'ods',
                        'CSV' => 'csv',
                    ],
                )
            )
            // ->add('sector', 
            //     ChoiceType::class,
            //     array(
            //         'expanded' => false,
            //         'empty_data' => '',
            //         'choices' => array_flip(Directory::$sectorValues),
            //     )
            // )
            ->add(
                'categories',
                ListingCategoryType::class,
                array(
                    'label' => 'listing_search.form.categories',
                    'mapped' => false,
                    'data' => $categories,
                    'block_name' => 'listing_categories',
                    'multiple' => true,
                    'placeholder' => 'listing_search.form.categories.empty_value',
                )
            )
            //->add('region',
            //    ChoiceType::class,
            //    array(
            //        'expanded' => false,
            //        'empty_data' => '',
            //        'choices' => array_flip(Directory::$regions),
            //    )
            //)
            ->add('structureType',
                ChoiceType::class,
                array(
                    'expanded' => false,
                    'empty_data' => '',
                    'choices' => array_flip(Directory::$kindValues),
                )
            )
            ->add('prestaType',
                ChoiceType::class,
                array(
                    'expanded' => false,
                    'empty_data' => '',
                    'choices' => array_flip(Directory::$prestaTypeValues),
                )
            )
            ->add('withAntenna',
                CheckboxType::class,
                array(
                    'label' => 'Inclure les antennes',
                    'required'=> false,
                )
            );
            // ->add('save', SubmitType::class, ['label' => 'Filtrer'])
            // ->add(
            //     'status',
            //     ChoiceType::class,
            //     array(
            //         'mapped' => false,
            //         /** @Ignore */
            //         'label' => false,
            //         'choices' => array_flip(Quote::getVisibleStatusValues()),
            //         'placeholder' => 'admin.quote.status.label',
            //         'translation_domain' => 'cocorico_quote',
            //     )
            // );
        //
        $builder
            ->add(
                'address',
                SearchType::class,
                array(
                    'label' => 'listing.search.form.address'
                )
            )
            ->add('lat', HiddenType::class)
            ->add('lng', HiddenType::class)
            ->add('country', HiddenType::class)
            ->add('area', HiddenType::class)
            ->add('department', HiddenType::class)
            ->add('city', HiddenType::class)
            ->add('postalCode', HiddenType::class)
            ->add('zip', HiddenType::class)
            ->add('addressType', HiddenType::class);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(
            array(
                'csrf_protection' => false,
                'translation_domain' => 'cocorico_quote',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'directory_filter';
    }
}
