<?php

declare(strict_types=1);

namespace Cundd\Rest\Documentation;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Reports\ReportInterface;
use TYPO3Fluid\Fluid\View\ViewInterface;

class HandlerReport implements ReportInterface
{
    private ViewInterface $view;

    private HandlerDescriptor $handlerDescriptor;

    public function __construct(HandlerDescriptor $handlerDescriptor)
    {
        $this->view = GeneralUtility::makeInstance(StandaloneView::class);;
        $this->handlerDescriptor = $handlerDescriptor;
    }

    /**
     * Returns the content for a report
     *
     * @return string A reports rendered HTML
     */
    public function getReport(): string
    {
        $this->view->setTemplatePathAndFilename(
            ExtensionManagementUtility::extPath('rest') . 'Resources/Private/Templates/HandlerReport.html'
        );
        $information = $this->handlerDescriptor->getInformation();
        ksort($information);
        $this->view->assign('information', $information);

        return $this->view->render();
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
