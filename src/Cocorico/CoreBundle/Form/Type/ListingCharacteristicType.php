<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\CoreBundle\Form\Type;

use Cocorico\CoreBundle\Entity\ListingCharacteristic;
use Cocorico\CoreBundle\Entity\ListingCharacteristicValue;
use Cocorico\CoreBundle\Repository\ListingCharacteristicRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;

class ListingCharacteristicType extends AbstractType
{
    private $locale;
    private $entityManager;

    /**
     * @param RequestStack  $requestStack
     * @param EntityManager $entityManager
     */
    public function __construct(RequestStack $requestStack, EntityManager $entityManager)
    {
        $this->locale = $requestStack->getCurrentRequest()->getLocale();
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                /** @var ListingCharacteristicRepository $characteristicsRepository */
                $characteristicsRepository = $this->entityManager->getRepository(
                    "CocoricoCoreBundle:ListingCharacteristic"
                );
                $characteristics = $characteristicsRepository->findAllTranslated($this->locale);

                $form = $event->getForm();
                $data = $event->getData();

                /** @var ListingCharacteristic $listingCharacteristic */
                foreach ($characteristics as $i => $listingCharacteristic) {
                    $id = $listingCharacteristic->getId();
                    $selected = array_key_exists($id, $data) ? $data[$id] : false;

                    $form->add(
                        $id,
                        ChoiceType::class,
                        array(
                            'data' => $selected,
                            'required' => false,
                            /** @Ignore */
                            'label' => $listingCharacteristic->getName(),
                            'label_attr' => array(
                                'group' => $listingCharacteristic->getListingCharacteristicGroup()->getName()
                            ),
                            'mapped' => false,
                            'choices' => $this->buildCharacteristicValuesChoices($listingCharacteristic),
                        )
                    );
                }
            }
        );
    }

    /**
     * @param ListingCharacteristic $listingCharacteristic
     * @return array
     */
    private function buildCharacteristicValuesChoices(ListingCharacteristic $listingCharacteristic)
    {
        $choices = array();
        $characteristics = $listingCharacteristic->getListingCharacteristicTypeValues();
        /** @var ListingCharacteristicValue $characteristic */
        foreach ($characteristics as $characteristic) {
            $choices[$characteristic->getName()] = $characteristic->getId();
        }

        return $choices;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'listing_characteristic';
    }
}
