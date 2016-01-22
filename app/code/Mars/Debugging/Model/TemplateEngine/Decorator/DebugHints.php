<?php
/**
 * Decorator that inserts debugging hints into the rendered block contents
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mars\Debugging\Model\TemplateEngine\Decorator;

use Magento\Developer\Model\TemplateEngine\Decorator\DebugHints as RootDebugHits;
use Magento\Framework\View\TemplateEngineInterface;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\AbstractBlock;
use \Magento\Framework\View\LayoutInterface;

/**
 * Class DebugHints
 *
 * @package Mars\Debugging\Model\TemplateEngine\Decorator
 */
class DebugHints extends RootDebugHits
{
    /**
     * @var \Magento\Framework\View\TemplateEngineInterface
     */
    private $_subject;

    /**
     * @var bool
     */
    private $_showBlockHints;

    /**
     * @param \Magento\Framework\View\TemplateEngineInterface $subject
     * @param bool $showBlockHints Whether to include block into the debugging information or not
     */
    public function __construct(TemplateEngineInterface $subject, $showBlockHints)
    {
        $this->_subject = $subject;
        $this->_showBlockHints = $showBlockHints;
    }

    /**
     * Insert debugging hints into the rendered block contents
     *
     * {@inheritdoc}
     */
    public function render(
        BlockInterface $block,
        $templateFile,
        array $dictionary = []
    )
    {
        $orgHtml = $this->_subject->render($block, $templateFile, $dictionary);
        if ($this->_showBlockHints) {
            $blockHits = $this->_renderBlockHints("", $block);
        }
        $templateHits = $this->_renderTemplateHints("", $templateFile);
        $result = $this->_renderBlockDescription($templateHits, $blockHits, $orgHtml);
        return $result;
    }

    /**
     * Insert template debugging hints into the rendered block contents
     *
     * @param string $blockHtml
     * @param string $templateFile
     *
     * @return string
     */
    protected function _renderTemplateHints($blockHtml, $templateFile)
    {
        return <<<HTML
<div class="debugging-hint-template-file"
     style="position: absolute;
            top: 0;
            padding: 2px 5px;
            font: normal 11px Arial;
            background: red; left: 0;
            color: white;
            white-space: nowrap;"
     onmouseover="this.style.zIndex = 999;"
     onmouseout="this.style.zIndex = 'auto';"
     title="{$templateFile}">
{$templateFile}
</div>
HTML;
    }

    /**
     * Insert block debugging hints into the rendered block contents
     *
     * @param string $blockHtml
     * @param \Magento\Framework\View\Element\BlockInterface $block
     *
     * @return string
     */
    protected function _renderBlockHints($blockHtml,BlockInterface $block)
    {
        $blockClass = get_class($block);
        $blockLayoutNamePath = $this->_getBlockNamePath($block);
        return <<<HTML
<div
    class="debugging-hint-block-class"
    style=" position: absolute; top: 0;
            padding: 2px 5px;
            font: normal 11px Arial;
            background: red;
            right: 0;
            color: white;
            white-space: nowrap;"
    onmouseover="this.style.zIndex = 999;"
    onmouseout="this.style.zIndex = 'auto';"
    title="{$blockClass}"
layout-name-path = "{$blockLayoutNamePath}"
>
    <div>class : {$blockClass}</div>
    <div>layout-name-path : {$blockLayoutNamePath}</div>
</div>
HTML;
    }

    /**
     * @param $templateHits
     * @param $blockHits
     * @param $orgHtml
     *
     * @return string
     */
    protected function _renderBlockDescription($templateHits,$blockHits,$orgHtml)
    {
        return <<<HTML
<div
    class="debugging-hints"
    style=" position: relative;
            border: 1px dotted red;
            margin: 6px 2px;
            padding: 18px 2px 2px 2px;"
    onmouseover="jQuery(this).children('.container').show();"
    onmouseout="jQuery(this).children('.container').hide();"; >
<div class="container" style="display:none">
    {$blockHits}
    {$templateHits}
</div>
    {$orgHtml}
</div>
HTML;
    }

    /**
     * @param AbstractBlock $block
     *
     * @return string
     */
    protected function _getBlockNamePath(AbstractBlock $block)
    {
        $layout = $block->getLayout();
        $blockName = $block->getNameInLayout();
        $path = $this->_getElementPath($layout, $blockName);
        return (isset($path)) ? $path . " / " . $blockName : $blockName;
    }

    /**
     * @param string $nameElement
     * @param LayoutInterface $layout
     *
     * @return null|string
     */
    protected function _getElementPath(LayoutInterface $layout, $nameElement)
    {
        $parentName = $layout->getParentName($nameElement);
        if(!$parentName)
            return null;

        $parentBlock = $layout->getBlock($parentName);
        $pathName = ($parentBlock) ? $parentName : "[{$parentName}]";

        $path = (isset($parentName) && $parentName !== false)
            ? $this->_getElementPath($layout, $parentName) : null;

        return (isset($path)) ? $path . " / " . $pathName : $pathName;
    }
}
