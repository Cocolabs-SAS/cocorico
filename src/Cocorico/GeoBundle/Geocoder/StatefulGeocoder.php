<?php

declare(strict_types=1);

/*
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

namespace Cocorico\GeoBundle\Geocoder;

use Cocorico\GeoBundle\Geocoder\Provider\ProviderInterface;
use Geocoder\Collection;
use Geocoder\Geocoder;
use Geocoder\Model\Bounds;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;

/**
 * Add reverseAsJson
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 * @author Cocolabs
 */
final class StatefulGeocoder implements Geocoder
{
    /**
     * @var string
     */
    protected $locale;

    /**
     * @var Bounds
     */
    protected $bounds;

    /**
     * @var int
     */
    protected $limit;

    /**
     * @var ProviderInterface
     */
    protected $provider;

    /**
     * @param ProviderInterface $provider
     * @param string            $locale
     */
    public function __construct(ProviderInterface $provider, string $locale = null)
    {
        $this->provider = $provider;
        $this->locale = $locale;
        $this->limit = Geocoder::DEFAULT_RESULT_LIMIT;
    }

    /**
     * {@inheritdoc}
     */
    public function geocode(string $value): Collection
    {
        $query = GeocodeQuery::create($value)
            ->withLimit($this->limit);

        if (!empty($this->locale)) {
            $query->withLocale($this->locale);
        }

        if (!empty($this->bounds)) {
            $query->withBounds($this->bounds);
        }

        return $this->provider->geocodeQuery($query);
    }

    /**
     * {@inheritdoc}
     */
    public function reverse(float $latitude, float $longitude): Collection
    {
        $query = ReverseQuery::fromCoordinates($latitude, $longitude)
            ->withLimit($this->limit);

        if (!empty($this->locale)) {
            $query = $query->withLocale($this->locale);
        }

        return $this->provider->reverseQuery($query);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseAsJson(float $latitude, float $longitude)
    {
        $query = ReverseQuery::fromCoordinates($latitude, $longitude)
            ->withLimit($this->limit);

        if (!empty($this->locale)) {
            $query = $query->withLocale($this->locale);
        }

        return $this->provider->reverseQueryAsJson($query);
    }


    /**
     * {@inheritdoc}
     */
    public function geocodeQuery(GeocodeQuery $query): Collection
    {
        $locale = $query->getLocale();
        if (empty($locale) && null !== $this->locale) {
            $query = $query->withLocale($this->locale);
        }

        $bounds = $query->getBounds();
        if (empty($bounds) && null !== $this->bounds) {
            $query = $query->withBounds($this->bounds);
        }

        return $this->provider->geocodeQuery($query);
    }

    /**
     * {@inheritdoc}
     */
    public function reverseQuery(ReverseQuery $query): Collection
    {
        $locale = $query->getLocale();
        if (empty($locale) && null !== $this->locale) {
            $query->withLocale($this->locale);
        }

        return $this->provider->reverseQuery($query);
    }

    /**
     * @param string $locale
     *
     * @return StatefulGeocoder
     */
    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @param Bounds $bounds
     *
     * @return StatefulGeocoder
     */
    public function setBounds(Bounds $bounds): self
    {
        $this->bounds = $bounds;

        return $this;
    }

    /**
     * @param int $limit
     *
     * @return StatefulGeocoder
     */
    public function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'stateful_geocoder';
    }
}
