<?php

declare(strict_types=1);

namespace Cundd\Rest\Access;

use Cundd\Rest\Access\Exception\InvalidConfigurationException;
use Cundd\Rest\Configuration\Access;
use Cundd\Rest\Configuration\ConfigurationProviderInterface;
use Cundd\Rest\Configuration\ResourceConfiguration;
use Cundd\Rest\Domain\Model\ResourceType;
use Cundd\Rest\Http\RestRequestInterface;
use Cundd\Rest\ObjectManagerInterface;
use OutOfBoundsException;

/**
 * The class determines the access for the current request
 */
class ConfigurationBasedAccessController extends AbstractAccessController
{
    /**
     * Access identifier to specify which methods DO NOT require authorization
     */
    public const ACCESS_NOT_REQUIRED = ['OPTIONS'];

    protected ConfigurationProviderInterface $configurationProvider;

    public function __construct(
        ConfigurationProviderInterface $configurationProvider,
        ObjectManagerInterface $objectManager,
    ) {
        parent::__construct($objectManager);
        $this->configurationProvider = $configurationProvider;
    }

    /**
     * @throws InvalidConfigurationException
     */
    public function getAccess(RestRequestInterface $request): Access
    {
        $access = $this->getAccessConfiguration($request);
        if (Access::RequireLogin === $access) {
            return $this->checkAuthentication($request);
        }

        return $access;
    }

    /**
     * Returns the configuration matching the given resource type
     */
    public function getConfigurationForResourceType(ResourceType $resourceType): ResourceConfiguration
    {
        return $this->configurationProvider->getResourceConfiguration($resourceType);
    }

    /**
     * Return if the given request needs authentication
     *
     * @throws InvalidConfigurationException
     */
    public function requestNeedsAuthentication(RestRequestInterface $request): bool
    {
        return Access::RequireLogin === $this->getAccessConfiguration($request);
    }

    /**
     * Return if the given request requires authorization
     */
    protected function requiresAuthorization(RestRequestInterface $request): bool
    {
        return !in_array(strtoupper($request->getMethod()), self::ACCESS_NOT_REQUIRED);
    }

    protected function getAccessConfiguration(RestRequestInterface $request): Access
    {
        if (!$this->requiresAuthorization($request)) {
            return Access::Allowed;
        }

        $configuration = $this->getConfigurationForRequest($request);

        return match (true) {
            $request->isWrite()     => $configuration->writeAccess,
            $request->isPreflight() => Access::Allowed,
            $request->isRead()      => $configuration->readAccess,
            default                 => throw new OutOfBoundsException('Request is neither write, read, nor a preflight request'),
        };
    }
}
