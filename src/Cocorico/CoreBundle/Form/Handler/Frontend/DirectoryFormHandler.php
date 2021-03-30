<?php
namespace Cocorico\CoreBundle\Form\Handler\Frontend;

use Cocorico\CoreBundle\Entity\Directory;
use Cocorico\CoreBundle\Model\Manager\DirectoryManager;
use Cocorico\UserBundle\Entity\User;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Handle Directory Form
 */

class DirectoryFormHandler
{
    protected $request;
    protected $directoryManager;
    /** @var User|null */
    private $user = null;

    /**
     * DirectoryFormHandler constructor.
     * @param TokenStorage         $securityTokenStorage
     * @param AuthorizationChecker $securityAuthChecker
     * @param RequestStack         $requestStack
     * @param DirectoryManager       $directoryManager
     * @throws \Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException
     */
    public function __construct(
        TokenStorage $securityTokenStorage,
        AuthorizationChecker $securityAuthChecker,
        RequestStack $requestStack,
        DirectoryManager $directoryManager
    ) {
        if ($securityAuthChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            $this->user = $securityTokenStorage->getToken()->getUser();
        }
        $this->request = $requestStack->getCurrentRequest();
        $this->directoryManager = $directoryManager;

    }

    /**
     * @return Directory
     * @throws AccessDeniedException
     */
    public function init($directory)
    {
        // $directory = new Directory();
        if (! $directory->hasUser($this->user)) {
            $directory->addUser($this->user);
        }
        $directory = $this->addImages($directory);
        $directory = $this->addClientImages($directory);
        $directory = $this->addCategories($directory);

        return $directory;
    }

    /**
     * Process form
     *
     * @param Form $form
     *
     * @throws \Symfony\Component\Form\Exception\RuntimeException
     */
    public function process($form)
    {
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $this->request->isMethod('POST') && $form->isValid()) {
            return $this->onSuccess($form);
        }

        return false;
    }
    /**
     * @param Form $form
     * @return bool
     * @throws \Symfony\Component\Form\Exception\RuntimeException
     */
    private function onSuccess(Form $form)
    {
        /** @var Directory $directory */
        $directory = $form->getData();
        $this->directoryManager->save($directory);

        return true;
    }




    /**
     * @param  Directory $directory
     * @throws AccessDeniedException
     * @return Directory
     */
    private function addImages(Directory $directory)
    {
        //Files to upload
        $imagesUploaded = $this->request->request->get("directory");
        $imagesUploaded = $imagesUploaded["image"]["uploaded"];

        if ($imagesUploaded) {
            $imagesUploadedArray = explode(",", trim($imagesUploaded, ","));
            $directory = $this->directoryManager->addImages(
                $directory,
                $imagesUploadedArray
            );
        }

        return $directory;
    }

    /**
     * @param  Directory $directory
     * @throws AccessDeniedException
     * @return Directory
     */
    private function addClientImages(Directory $directory)
    {
        //Files to upload
        $imagesUploaded = $this->request->request->get("directory");
        $imagesUploaded = $imagesUploaded["clientImage"]["uploaded"];

        if ($imagesUploaded) {
            $imagesUploadedArray = explode(",", trim($imagesUploaded, ","));
            $directory = $this->directoryManager->addClientImages(
                $directory,
                $imagesUploadedArray
            );
        }

        return $directory;
    }

    /**
     * Add selected categories and corresponding fields values from post parameters while directory deposit
     *
     * @param  Directory $directory
     * @return Directory
     */
    public function addCategories(Directory $directory)
    {
        $categories = $this->request->request->get("directory_categories");
        dump($categories);

        $directoryCategories = isset($categories["directoryListingCategories"]) ? $categories["directoryListingCategories"] : array();
        $directoryCategoriesValues = isset($categories["categoriesFieldsSearchableValuesOrderedByGroup"]) ? $categories["categoriesFieldsSearchableValuesOrderedByGroup"] : array();

        if ($categories) {
            $directory = $this->directoryManager->addCategories(
                $directory,
                $directoryCategories,
                $directoryCategoriesValues
            );
        }

        return $directory;
    }



}
