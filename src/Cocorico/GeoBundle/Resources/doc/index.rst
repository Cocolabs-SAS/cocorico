GeoBundle
=========

Requirements
------------

If the distanceMatrix functionality is used then "guzzlehttp/guzzle": "^5.3" dependency is required.

Geocoding object format used internally
---------------------------------------

::

stdClass Object
(

    [formatted_address] => 8 Rue Saint - Marc, 75002 Paris, France
   
    [location_type] => ROOFTOP
    [viewport] => stdClass Object
    (
        [Ea] => stdClass Object
        (
            [k] => 48.869021319709
            [j] => 48.871719280292
        )
        [va] => stdClass Object
        (
            [j] => 2.3403152197085
            [k] => 2.3430131802916
        )
    )

    [location] => stdClass Object
    (
        [k] => 48.8703703
        [B] => 2.3416642
    )

    [lat] => 48.8703703
    [lng] => 2.3416642



    [en] => stdClass Object
    (
        [street_number] => 8
        [street_number_short] => 8
        [route] => Rue Saint - Marc
        [route_short] => Rue Saint - Marc
        [locality] => Paris
        [locality_short] => Paris
        [political] => France
        [political_short] => FR
        [administrative_area_level_2] => Paris
        [administrative_area_level_2_short] => 75
        [administrative_area_level_1] => ile - de - France
        [administrative_area_level_1_short] => IDF
        [country] => France
        [country_short] => FR
        [postal_code] => 75002
        [postal_code_short] => 75002
    )

    [fr] => stdClass Object
    (
        [street_number] => 8
        [street_number_short] => 8
        [route] => Rue Saint - Marc
        [route_short] => Rue Saint - Marc
        [locality] => Paris
        [locality_short] => Paris
        [political] => France
        [political_short] => FR
        [administrative_area_level_2] => Paris
        [administrative_area_level_2_short] => 75
        [administrative_area_level_1] => ÃŽle - de - France
        [administrative_area_level_1_short] => IDF
        [country] => France
        [country_short] => FR
        [postal_code] => 75002
        [postal_code_short] => 75002
    )
)