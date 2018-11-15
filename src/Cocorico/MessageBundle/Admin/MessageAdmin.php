<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\MessageBundle\Admin;

use Cocorico\MessageBundle\Entity\Message;
use Cocorico\UserBundle\Repository\UserRepository;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;


class MessageAdmin extends Admin
{
    protected $translationDomain = 'SonataAdminBundle';
    protected $baseRoutePattern = 'message';
    protected $locales;

    // setup the default sort column and order
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt'
    );

    public function setLocales($locales)
    {
        $this->locales = $locales;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var Message $message */
        $message = $this->getSubject();

        $senderQuery = null;
        if ($message) {
            /** @var UserRepository $userRepository */
            $userRepository = $this->modelManager->getEntityManager('CocoricoUserBundle:User')
                ->getRepository('CocoricoUserBundle:User');

            $senderQuery = $userRepository->getFindOneQueryBuilder($message->getSender()->getId());
        }

        $formMapper
            ->add(
                'sender',
                'sonata_type_model',
                array(
                    'query' => $senderQuery,
                    'read_only' => true,
                    'disabled' => true,
                )
            )
            ->add(
                'createdAt',
                null,
                array(
                    'read_only' => true,
                    'disabled' => true,
                )
            )
            ->add(
                'body',
                null,
                array(
                    'disabled' => true,
                    'read_only' => true,
                )
            )
            ->end();
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
        $collection->remove('delete');
    }
}
