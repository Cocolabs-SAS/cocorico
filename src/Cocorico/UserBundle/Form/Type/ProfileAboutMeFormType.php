<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\UserBundle\Form\Type;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use Cocorico\CoreBundle\Form\Type\ImageType;
use Cocorico\CoreBundle\Form\Type\LanguageFilteredType;
use Cocorico\UserBundle\Entity\User;
use Cocorico\UserBundle\Entity\UserImage;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Translation\TranslationContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\LanguageType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;

class ProfileAboutMeFormType extends AbstractType implements TranslationContainerInterface
{
    private $class;
    private $request;
    private $locale;
    private $locales;
    /**
     * @var array|string uploaded files
     */
    protected $uploaded;

    /**
     * @param string       $class The User class name
     * @param RequestStack $requestStack
     * @param array        $locales
     */
    public function __construct($class, RequestStack $requestStack, $locales)
    {
        $this->class = $class;
        $this->request = $requestStack->getCurrentRequest();
        $this->locale = $this->request->getLocale();
        $this->locales = $locales;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var User $user */
        $user = $builder->getData();

        //Translations fields
        $descriptions = array();
        foreach ($this->locales as $i => $locale) {
            $descriptions[$locale] = array(
                /** @Ignore */
                'label' => false,
                'constraints' => array(new NotBlank()),
                'attr' => array(
                    'placeholder' => 'auto'
                )
            );
        }

        $builder->add(
            'translations',
            TranslationsType::class,
            array(
                'locales' => $this->locales,
                'required_locales' => $this->locales,
                'fields' => array(
                    'description' => array(
                        'field_type' => 'textarea',
                        'locale_options' => $descriptions,
                    ),
                ),
                /** @Ignore */
                'label' => false
            )
        );

        $builder
            ->add(
                'image',
                ImageType::class
            )
            ->add(
                'images',
                CollectionType::class,
                array(
                    'allow_delete' => true,
                    'entry_type' => UserImageType::class,
                    /** @Ignore */
                    'label' => false
                )
            )
            ->add(
                'language',
                LanguageType::class,
                array(
                    'mapped' => false,
                    'label' => 'cocorico.language',
                    'preferred_choices' => array("en", "fr", "es", "de", "it", "ar", "zh", "ru"),
                    'placeholder' => 'user.about.language.select',
                    'required' => false
                )
            )
            ->add(
                'languages',
                CollectionType::class,
                array(
                    'allow_delete' => true,
                    'allow_add' => true,
                    'by_reference' => false,
                    'entry_type' => UserLanguageType::class,
                    /** @Ignore */
                    'label' => false
                )
            )
            ->add(
                'motherTongue',
                LanguageType::class,
                array(
                    'label' => 'cocorico.motherTongue',
                    'preferred_choices' => array("en", "fr", "es", "de", "it", "ar", "zh", "ru"),
                    'data' => $user->getMotherTongue() ? $user->getMotherTongue() : $this->locale
                )
            )
            ->add(
                'fromLang',
                LanguageFilteredType::class,
                array(
                    'mapped' => false,
                    'label' => 'cocorico.from',
                    'data' => $this->locale
                )
            )
            ->add(
                'toLang',
                LanguageFilteredType::class,
                array(
                    'mapped' => false,
                    'label' => 'cocorico.to',
                    'data' => LanguageFilteredType::getLocaleTo($this->locales, $this->locale),
                )
            );

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $data = $event->getData();
                $data = $data ?: array();
                if (array_key_exists('uploaded', $data["image"])) {
                    // capture uploaded files and store them for onSubmit event
                    $this->uploaded = $data["image"]['uploaded'];
                }
            }
        );


        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $event) {
                /** @var User $user */
                $user = $event->getData();

                if ($this->uploaded) {
                    $nbImages = $user->getImages()->count();
                    //Add new images
                    $imagesUploadedArray = explode(",", trim($this->uploaded, ","));
                    foreach ($imagesUploadedArray as $i => $image) {
                        $userImage = new UserImage();
                        $userImage->setuser($user);
                        $userImage->setName($image);
                        $userImage->setPosition($nbImages + $i + 1);
                        $user->addImage($userImage);
                    }

                    $event->setData($user);
                }
            }
        );
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => $this->class,
                'csrf_token_id' => 'profile',
                'translation_domain' => 'cocorico_user',
                'constraints' => new Valid(),
                'validation_groups' => array('CocoricoProfile'),
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'user_profile_about_me';
    }

    /**
     * JMS Translation messages
     *
     * @return array
     */
    public static function getTranslationMessages()
    {
        $messages[] = new Message("cocorico.en", 'cocorico_user');
        $messages[] = new Message("cocorico.fr", 'cocorico_user');
        $messages[] = new Message("user_translations_en_description_placeholder", 'cocorico_user');
        $messages[] = new Message("user_translations_fr_description_placeholder", 'cocorico_user');

        return $messages;
    }

}
