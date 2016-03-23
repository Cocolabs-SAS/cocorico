<?php

/*
 * This file is part of the Cocorico package.
 *
 * (c) Cocolabs SAS <contact@cocolabs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cocorico\ConfigBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ParameterAdmin extends Admin
{
    protected $translationDomain = 'SonataAdminBundle';
    protected $baseRoutePattern = 'parameter';

    // setup the default sort column and order
    protected $datagridValues = array(
        '_sort_order' => 'ASC',
        '_sort_by' => 'name'
    );


    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with(
                'admin.parameter.title',
                array(
                    'description' => 'admin.parameters.warning',
                    'translation_domain' => 'SonataAdminBundle'
                )
            )
            ->add(
                'name',
                null,
                array(
                    'label' => 'admin.parameter.name.label',
                    'disabled' => true
                )
            )
            ->add(
                'value',
                null,
                array(
                    'label' => 'admin.parameter.value.label',
                    'required' => false
                )
            )
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add(
                'name',
                null,
                array('label' => 'admin.parameter.name.label')
            )
            ->add(
                'value',
                null,
                array('label' => 'admin.parameter.value.label')
            );
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add(
                'name',
                null,
                array('label' => 'admin.parameter.name.label')
            )
            ->add(
                'value',
                null,
                array('label' => 'admin.parameter.value.label')
            );

        $listMapper->add(
            '_action',
            'actions',
            array(
                'actions' => array(
                    'show' => array(),
//                    'create' => array(),
                    'edit' => array(),
//                    'delete' => array(),
                )
            )
        );
    }

    public function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with(
                'Parameter',
                array(
                    'class' => 'col-md-8',
                    'box_class' => 'box box-solid box-danger',
                    'description' => '',
                )
            )
            ->add('name')
            ->add('value')
            ->end();
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        unset($actions["delete"]);

        return $actions;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
        $collection->remove('delete');
    }

    /**
     * @param mixed $parameter
     * @return bool
     */
    public function postUpdate($parameter)
    {
        return $this->clearCache();
    }

    /**
     * Clear cache
     *
     * @return bool
     */
    private function clearCache()
    {
        $kernel = $this->getConfigurationPool()->getContainer()->get('kernel');

        //Clear cache
        $php = 'php';
        if (file_exists("/usr/bin/php54")) {//tmp
            $php = 'php54';
        }
        $rootDir = $kernel->getRootDir();
        $command = $php . ' ' . $rootDir . '/console cache:clear --env=' . $kernel->getEnvironment();;

        $process = new Process($command);
        try {
            $process->mustRun();
            $content = $process->getOutput();
        } catch (ProcessFailedException $e) {
            $content = $e->getMessage();

            return false;
        }

//        $this->getRequest()->getSession()->getFlashBag()->add("success", $content);

        return true;
    }
}
