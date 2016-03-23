<?php
// app/tests.bootstrap.php

if (isset($_ENV['BOOTSTRAP_DB_ENV'])) {

    passthru(
        sprintf(
            'php "%s/console" doctrine:schema:update --force --env=%s',
            __DIR__,
            "test"
        )
    );
//    passthru(sprintf(
//            'php "%s/console" doctrine:fixtures:load --append --env=%s',
//            __DIR__,
//            $_ENV['BOOTSTRAP_DB_ENV']
//        ));
}

require __DIR__ . '/bootstrap.php.cache';