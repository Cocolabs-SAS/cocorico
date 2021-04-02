<?php
namespace Cocorico\CoreBundle\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class DirectoryCheckRequest {
    protected $siret;
    protected $brand;
    protected $name;
    protected $id;
    protected $isXmlHttpRequest = false;
    
    public function __construct(RequestStack $requestStack)
    {
        //Params
        $this->requestStack = $requestStack;
        $this->request = $this->requestStack->getCurrentRequest();
        if ($this->request) {
            if ($this->request->isXmlHttpRequest()) {
                $this->isXmlHttpRequest = true;
            }
        }
    }

    public function getSiret()
    {
        return $this->siret;
    }

    public function setSiret($siret)
    {
        return $this->siret = $siret;
    }

    public function getBrand()
    {
        return $this->brand;
    }

    public function setBrand($brand)
    {
        return $this->brand = $brand;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        return $this->name = $name;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        return $this->id = $id;
    }

    /**
     * @return boolean
     */
    public function getIsXmlHttpRequest()
    {
        return $this->isXmlHttpRequest;
    }

    /**
     * @param boolean $isXmlHttpRequest
     */
    public function setIsXmlHttpRequest($isXmlHttpRequest)
    {
        $this->isXmlHttpRequest = $isXmlHttpRequest;
    }



}
