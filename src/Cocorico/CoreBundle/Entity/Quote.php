<?php

namespace Cocorico\CoreBundle\Entity;

class Quote {
    private $id;
    private $listing;
    private $frequency_hours;
    private $frequency_period;
    private $surface_m2;
    private $surface_type;
    private $communication;

    public function getId()
    {
        return $this->id;
    }

    public function setListing(Listing $listing)
    {
        $this->listing = $listing;

        return $this;
    }

    public function getListing()
    {
        return $this->listing;
    }

    public function getFrequencyHours()
    {
        return $this->frequency_hours;
    }
    public function setFrequencyHours($hours)
    {
        // ok
    }

    public function getFrequencyPeriod()
    {
        return $this->frequency_period;
    }
    public function setFrequencyPeriod($period)
    {
        // ok
    }

    public function getSurfaceM2()
    {
        return $this->surface_m2;
    }
    public function setSurfaceM2($surface)
    {
        // ok
    }

    public function getSurfaceType()
    {
        return $this->surface_type;
    }
    public function setSurfaceType($type)
    {
        // ok
    }

    public function getCommunication()
    {
        return $this->surface_type;
    }
    public function setCommunication($type)
    {
        // ok
    }
}
