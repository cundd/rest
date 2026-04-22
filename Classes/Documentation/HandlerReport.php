<?php

declare(strict_types=1);

namespace Cundd\Rest\Documentation;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Reports\RequestAwareReportInterface;

/**
 * Custom report
 *
 * Version for TYPO3 v13
 */
class HandlerReport implements RequestAwareReportInterface
{
    public function __construct(
        private readonly SiteFinder $siteFinder,
        private readonly HandlerDescriptor $handlerDescriptor,
        private readonly ViewFactoryInterface $viewFactory,
    ) {
    }

    /**
     * Returns the content for a report
     *
     * @return string A reports rendered HTML
     */
    public function getReport(?ServerRequestInterface $request = null): string
    {
        $viewFactoryData = new ViewFactoryData(
            templateRootPaths: ['EXT:rest/Resources/Private/Templates'],
            partialRootPaths: [],
            layoutRootPaths: ['EXT:rest/Resources/Private/Fallback/Layouts'],
            request: $request,
        );

        $view = $this->viewFactory->create($viewFactoryData);

        $information = [];
        foreach ($this->siteFinder->getAllSites() as $site) {
            $information = array_merge(
                $information,
                $this->handlerDescriptor->getInformation($site)
            );
        }
        $view->assign('information', $information);

        return $view->render('HandlerReport');
    }

    public function getIdentifier(): string
    {
        return 'cundd-rest';
    }

    public function getTitle(): string
    {
        return 'REST';
    }

    public function getDescription(): string
    {
        return 'Get a status report about the REST integration';
    }

    public function getIconIdentifier(): string
    {
        return 'module-reports';
    }
}
