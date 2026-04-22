<?php

declare(strict_types=1);

namespace Cundd\Rest\DataProvider;

use Cundd\Rest\Http\RestRequestInterface;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

interface DataProviderInterface
{
    /**
     * Return all Domain Models for the given API resource type
     *
     * @return object[]|DomainObjectInterface[]|QueryResultInterface<mixed,DomainObjectInterface>
     */
    public function fetchAllModels(RestRequestInterface $request): iterable;

    /**
     * Return the number of all Domain Models for the given API resource type
     */
    public function countAllModels(RestRequestInterface $request): int;

    /**
     * Return a Domain Model for the given API resource type and data
     *
     * This method will load existing models
     *
     * @param int|array<string,mixed>|string $identifier Data of the new model or it's UID
     *
     * @return object|DomainObjectInterface|null Returns the Domain Model or NULL if it was not found
     */
    public function fetchModel(
        RestRequestInterface $request,
        int|array|string $identifier,
    ): ?object;

    /**
     * Create a new Domain Model with the given data
     *
     * Implementations are free to decide if identifiers are accepted (e.g. an exception will be thrown for Extbase
     * Models if the property `uid` or `__identity` is given)
     *
     * @param array<string,mixed> $data Data of the new model
     *
     * @return object|null Return the created Model on success otherwise an Exception
     */
    public function createModel(
        RestRequestInterface $request,
        array $data,
    ): ?object;

    /**
     * Converts the data into an instance of the Domain Model for the Resource Type
     *
     * @param array<string,mixed> $data
     *
     * @return object|DomainObjectInterface|null
     */
    public function convertIntoModel(
        RestRequestInterface $request,
        array $data,
    ): ?object;

    /**
     * Extract the data from the given Model or one of it's properties
     *
     * @return array<string,mixed>|int|bool|string|float|null
     */
    public function getModelData(
        RestRequestInterface $request,
        mixed $model,
    ): mixed;

    /**
     * Return the property data from the given Model
     *
     * @param non-empty-string             $propertyParameter
     * @param object|DomainObjectInterface $model
     */
    public function getModelProperty(
        RestRequestInterface $request,
        object $model,
        string $propertyParameter,
    ): mixed;

    /**
     * Add or update the given Model in the repository
     *
     * @param object|DomainObjectInterface $model
     */
    public function saveModel(
        RestRequestInterface $request,
        object $model,
    ): void;

    /**
     * Remove the given model from the repository for the given API resource type
     *
     * @param object|DomainObjectInterface $model
     */
    public function removeModel(
        RestRequestInterface $request,
        object $model,
    ): void;
}
