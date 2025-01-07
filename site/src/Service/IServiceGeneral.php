<?php

namespace App\Trellotrolle\Service;

use App\Trellotrolle\Service\Exception\ServiceException;

interface IServiceGeneral
{
    /**
     * @throws ServiceException
     */
    function checkConnexionEtAreSet(array $attribute): void;
    /**
     * @throws ServiceException
     */
    function checkIssetAndNotNull(array $requestParams): void;

    /**
     * @throws ServiceException
     */
    function checkConnexion(): void;

    /**
     * @throws ServiceException
     */
    public function checkIssetAndNotNullForObjectsArray(array $objects): void;
}