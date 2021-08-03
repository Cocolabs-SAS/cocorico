<?php
namespace Cocorico\CoreBundle\Form\Type\Frontend;

use Cocorico\CoreBundle\Entity\Directory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Cocorico\CoreBundle\Form\Type\ListingCategoryType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DirectoryQuickFilterType extends AbstractType
{
     /**
     * @param EntityManager            $entityManager
     * @param RequestStack             $requestStack
     * @param EventDispatcherInterface $dispatcher
     * @param array                    $parameters
     */
    public function __construct(
        EntityManager $entityManager,
        EventDispatcherInterface $dispatcher,
        $parameters
    ) {
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
        $parameters = $parameters["parameters"];
    }



    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
            ->add(
                'sector',
                ListingCategoryType::class,
                array(
                    'label' => 'listing_search.form.categories',
                    'mapped' => false,
                    'data' => $categories,
                    'block_name' => 'listing_categories',
                    'multiple' => true,
                    'placeholder' => 'listing_search.form.categories.empty_value',
                    'required' => false,
                )
            )
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
            ->add('region', HiddenType::class)
            ->add('postalCode', HiddenType::class)
            ->add('zip', HiddenType::class)
            ->add('addressType', HiddenType::class)
            ->add('serialSectors', HiddenType::class);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(
            array(
                'allow_extra_fields' => true,
                'csrf_protection' => false,
                'translation_domain' => 'cocorico_quote',
            )
        );
    }

}
