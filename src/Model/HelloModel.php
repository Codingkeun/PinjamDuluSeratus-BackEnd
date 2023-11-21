<?php

declare(strict_types=1);

/*
 * HelloModel
 * Author : Cecep Rokani
*/

namespace App\Model;

final class HelloModel
{
    public function getHello()
    {
        $data = "Pinjam Seratus Back End API Version 1.0";

        return $data;
    }
}
