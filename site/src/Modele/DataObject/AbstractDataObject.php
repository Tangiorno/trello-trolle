<?php

namespace App\Trellotrolle\Modele\DataObject;

abstract class AbstractDataObject implements \JsonSerializable
{

    public abstract function formatTableau(): array;

}
