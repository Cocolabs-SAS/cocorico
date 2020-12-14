<?php

namespace Cocorico\CoreBundle\Entity;

class DirectorySort
{
    protected $sector;
    protected $postalCode;
    protected $structureType;
    protected $prestaType;

    /* Sector 
     */
    public function getSector()
    {
        return $this->sector;
    }

    public function setSector($sector)
    {
        $this->sector = $sector;
    }

    /* Postal Code
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
    }

    /* Structure Type
     */
    public function getStructureType()
    {
        return $this->structureType;
    }

    public function setStructureType($structureType)
    {
        $this->structureType = $structureType;
    }

    /* Presta Type
     */
    public function getPrestaType()
    {
        return $this->prestaType;
    }

    public function setPrestaType($prestaType)
    {
        $this->prestaType = $prestaType;
    }
}
