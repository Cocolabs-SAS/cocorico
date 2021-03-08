#!/bin/bash
# Set entity as parameter
php bin/console doctrine:generate:entities Cocorico/CoreBundle/Entity/$1
