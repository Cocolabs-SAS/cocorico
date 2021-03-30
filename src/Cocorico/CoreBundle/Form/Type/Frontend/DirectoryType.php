<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Form\Type\Frontend;

use Cocorico\CoreBundle\Entity\Directory;
use Cocorico\CoreBundle\Event\DirectoryFormBuilderEvent;
use Cocorico\CoreBundle\Event\DirectoryFormEvents;
use Cocorico\CoreBundle\Form\Type\ImageType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Cocorico\CoreBundle\Model\Manager\DirectoryManager;
use Cocorico\CoreBundle\Form\Type\DirectoryDirectoryCharacteristicType;


/**
 * Class DirectoryNewType
 * Categories are created trough ajax in DirectoryNewCategoriesType.
 */
class DirectoryType extends AbstractType
{
    private $request;
    protected $dispatcher;
    protected $lem;

    /**
     * @param RequestStack             $requestStack
     * @param array                    $locales
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        RequestStack $requestStack,
        DirectoryManager $lem,
        EventDispatcherInterface $dispatcher
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->dispatcher = $dispatcher;
        $this->lem = $lem;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Directory $directory */
        $directory = $builder->getData();

        $builder
            ->add(
                'siret',
                TextType::class,
                array(
                    'disabled' => 'disabled',
                )
            )
            ->add(
                'brand',
                TextType::class,
                array(
                    'disabled' => 'disabled',
                )
            )
            ->add(
                'name',
                TextType::class,
                array(
                    'disabled' => 'disabled',
                )
            )
            ->add(
                'kind',
                TextType::class,
                array(
                    'disabled' => 'disabled',
                )
            )
            ->add(
                'region',
                TextType::class,
                array(
                    'disabled' => 'disabled',
                )
            )
            ->add(
                'postCode',
                TextType::class,
                array(
                    'disabled' => 'disabled',
                )
            )
            ->add(
                'range',
                IntegerType::class,
                array(
                    'label' => 'directory.form.range',
                    'required' => false,
                )
            )
            ->add(
                'polRange',
                ChoiceType::class,
                array(
                    'choices' => array_flip(Directory::$polRangeValues),
                    'label' => 'Périmètre intervention',
                    'translation_domain' => 'cocorico_directory',
                    'expanded' => true,
                    'required' => true
                )
            )
            ->add(
                'website',
                UrlType::class,
                array(
                    'label' => 'directory.form.url',
                    'required' => false,
                )
            )
            # ->add(
            #     'presta_type',
            #     ChoiceType::class,
            #     array(
            #         'choices' => array_flip(Directory::$prestaTypeValues),
            #         'label' => 'Type prestation',
            #         'translation_domain' => 'cocorico_directory',
            #         'expanded' => true,
            #         'required' => true
            #     )
            # )
            ->add(
                'image',
                ImageType::class
            )
            ->add(
                'clientImage',
                ImageType::class
            );

        // Dispatch directory_NEW_FORM_BUILD Event. Listener listening this event can add fields and validation
        // Used for example to add fields to new directory form
        $this->dispatcher->dispatch(
            DirectoryFormEvents::DIRECTORY_NEW_FORM_BUILD,
            new DirectoryFormBuilderEvent($builder)
        );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'allow_extra_fields' => true,
                'data_class' => 'Cocorico\CoreBundle\Entity\Directory',
                'csrf_token_id' => 'directory_new',
                'translation_domain' => 'cocorico_directory',
                'constraints' => new Valid(),
                //'validation_groups' => array('Directory'),
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'directory_new';
    }

    /**
     * JMS Translation messages.
     *
     * @return array
     */
    public static function getTranslationMessages()
    {
        $messages = array();

        return $messages;
    }
}
