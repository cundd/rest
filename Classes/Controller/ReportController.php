<?php

declare(strict_types=1);

namespace Cundd\Rest\Controller;

use Cundd\Rest\Documentation\HandlerDescriptor;
use Cundd\Rest\Exception\InvalidConfigurationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

#[AsController]
final class ReportController extends ActionController
{
    public function __construct(
        private readonly ModuleTemplateFactory $moduleTemplateFactory,
        private readonly SiteFinder $siteFinder,
        private readonly HandlerDescriptor $handlerDescriptor,
    ) {
    }

    public function handleRequest(
        ServerRequestInterface $request,
    ): ResponseInterface {
        $moduleTemplate = $this->moduleTemplateFactory->create($request);

        $information = [];
        foreach ($this->siteFinder->getAllSites() as $site) {
            $siteConfiguration = null;

            try {
                $siteConfiguration = $this->handlerDescriptor->getInformation($site);
            } catch (InvalidConfigurationException $e) {
            }

            if ($siteConfiguration) {
                $information = array_merge(
                    $information,
                    $siteConfiguration
                );
            }
        }

        $moduleTemplate->assign('information', $information);

        return $moduleTemplate->renderResponse('HandlerReport');
    }
}
