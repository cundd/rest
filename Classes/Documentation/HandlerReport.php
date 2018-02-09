<?php


namespace Cundd\Rest\Documentation;


use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Reports\Controller\ReportController;
use TYPO3\CMS\Reports\ReportInterface;

class HandlerReport implements ReportInterface
{
    /**
     * @var StandaloneView
     * @inject
     */
    private $view;

    /**
     * @var HandlerDescriptor
     * @inject
     */
    private $handlerDescriptor;

    /**
     * HandlerReport constructor
     *
     * @param ReportController  $reportController
     * @param ViewInterface     $view
     * @param HandlerDescriptor $handlerDescriptor
     */
    public function __construct(
        ReportController $reportController,
        ViewInterface $view = null,
        HandlerDescriptor $handlerDescriptor = null
    ) {
        $om = GeneralUtility::makeInstance(ObjectManager::class);
        $this->view = $view ?: $om->get(StandaloneView::class);
        $this->handlerDescriptor = $handlerDescriptor ?: $om->get(HandlerDescriptor::class);
    }

    /**
     * Returns the content for a report
     *
     * @return string A reports rendered HTML
     */
    public function getReport()
    {
        $this->view->setTemplatePathAndFilename(
            ExtensionManagementUtility::extPath('rest') . 'Resources/Private/Templates/HandlerReport.html'
        );
        $information = $this->handlerDescriptor->getInformation();
        ksort($information);
        $this->view->assign('information', $information);

        return $this->view->render();
    }
}
