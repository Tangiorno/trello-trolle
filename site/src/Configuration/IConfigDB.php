<?php

namespace App\Trellotrolle\Configuration;

interface IConfigDB
{
    public function getLogin() : string;
    public function getMotDePasse() : string;
    public function getDSN() : string;
    public function getOptions() : array;

}