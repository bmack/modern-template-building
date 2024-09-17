<?php

declare(strict_types=1);

/*
 * This file is part of TYPO3 CMS-based extension "modern_template_building" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

namespace Bmack\ModernTemplateBuilding\ContentObject;

use TYPO3\CMS\Core\Type\File\ImageInfo;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\AbstractContentObject;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Resource\FilePathSanitizer;

/**
 * Contains FILE class object.
 */
class FileContentObject extends AbstractContentObject
{
    /**
     * Rendering the cObject, FILE
     *
     * @param array $conf Array of TypoScript properties
     * @return string Output
     */
    public function render($conf = [])
    {
        $theValue = '';
        $file = isset($conf['file.']) ? $this->cObj->stdWrap($conf['file'], $conf['file.']) : $conf['file'];
        try {
            $file = GeneralUtility::makeInstance(FilePathSanitizer::class)->sanitize($file, true);
            if (str_starts_with($file, 'EXT:')) {
                $file = GeneralUtility::getFileAbsFileName($file);
            }
            if (file_exists($file)) {
                $fileInfo = GeneralUtility::split_fileref($file);
                $extension = $fileInfo['fileext'];
                if ($extension === 'jpg' || $extension === 'jpeg' || $extension === 'gif' || $extension === 'png') {
                    $imageInfo = GeneralUtility::makeInstance(ImageInfo::class, $file);
                    $altParameters = trim($this->cObj->getAltParam($conf, false));
                    $theValue = '<img src="'
                        . htmlspecialchars($this->getTypoScriptFrontendController()->absRefPrefix . $file)
                        . '" width="' . (int)$imageInfo->getWidth() . '" height="' . (int)$imageInfo->getHeight()
                        . '"' . $this->cObj->getBorderAttr(' border="0"') . ' ' . $altParameters . ' />';
                } elseif (filesize($file) < 1024 * 1024) {
                    $theValue = file_get_contents($file);
                }
            }
        } catch (\TYPO3\CMS\Core\Resource\Exception) {
            // do nothing
        }
        $linkWrap = isset($conf['linkWrap.']) ? $this->cObj->stdWrap($conf['linkWrap'], $conf['linkWrap.']) : (isset($conf['linkWrap']) ? $conf['linkWrap'] : '');
        if ($linkWrap) {
            $theValue = $this->cObj->linkWrap($theValue, $linkWrap);
        }
        $wrap = isset($conf['wrap.']) ? $this->cObj->stdWrap($conf['wrap'], $conf['wrap.']) : (isset($conf['wrap']) ? $conf['wrap'] : '');
        if ($wrap) {
            $theValue = $this->cObj->wrap($theValue, $wrap);
        }
        if (isset($conf['stdWrap.'])) {
            $theValue = $this->cObj->stdWrap($theValue, $conf['stdWrap.']);
        }
        return $theValue;
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}

