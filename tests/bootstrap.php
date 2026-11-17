<?php

declare(strict_types=1);

/**
 * PHPUnit bootstrap. Deliberately minimal — the unit tests target pure
 * logic (MatchingService's compatibility table, Validator, EligibilityService)
 * and never touch the database, so there is no app bootstrap/session/env
 * dependency here, only the autoloader.
 */

require dirname(__DIR__) . '/vendor/autoload.php';
