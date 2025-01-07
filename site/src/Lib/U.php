<?php

namespace App\Trellotrolle\Lib;

use JetBrains\PhpStorm\NoReturn;

class U
{
    public static function p(mixed $obj): void
    {
        echo "<pre>";
        print_r($obj);
        echo "</pre>";
    }

    #[NoReturn] public static function pd(mixed $obj): void
    {
        self::p($obj);
        die();
    }
}