<?php


namespace Cundd\Rest\ViewHelpers;


use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

class GetClassViewHelper extends AbstractViewHelper
{
    public function initializeArguments()
    {
        $this->registerArgument('object', 'object', 'Object to get the class name', false);

        parent::initializeArguments();
    }

    /**
     * Return the given object's class name
     *
     * @return string
     */
    public function render()
    {
        if (isset($this->arguments['object'])) {
            return get_class($this->arguments['object']);
        }
        return get_class($this->renderChildren());
    }
}
