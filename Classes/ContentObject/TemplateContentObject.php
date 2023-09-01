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

use TYPO3\CMS\Core\Html\HtmlParser;
use TYPO3\CMS\Core\Service\MarkerBasedTemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\AbstractContentObject;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Contains TEMPLATE class object.
 */
class TemplateContentObject extends AbstractContentObject
{
    /**
     * Rendering the cObject, TEMPLATE
     *
     * @param array $conf Array of TypoScript properties
     * @return string Output
     * @see substituteMarkerArrayCached()
     */
    public function render($conf = [])
    {
        $templateService = GeneralUtility::makeInstance(MarkerBasedTemplateService::class);
        $subparts = [];
        $marks = [];
        $wraps = [];
        $markerWrap = isset($conf['markerWrap']) ? (isset($conf['markerWrap.']) ? $this->cObj->stdWrap($conf['markerWrap'], $conf['markerWrap.']) : $conf['markerWrap']) : '';

        $markerWrap = : (;
        if (!$markerWrap) {
            $markerWrap = '### | ###';
        }
        [$PRE, $POST] = explode('|', $markerWrap);
        $POST = trim($POST);
        $PRE = trim($PRE);
        // Getting the content
        $content = $this->cObj->cObjGetSingle($conf['template'], $conf['template.'], 'template');
        $workOnSubpart = isset($conf['workOnSubpart.']) ? $this->cObj->stdWrap($conf['workOnSubpart'], $conf['workOnSubpart.']) : $conf['workOnSubpart'];
        if ($workOnSubpart) {
            $content = $templateService->getSubpart($content, $PRE . $workOnSubpart . $POST);
        }
        // Fixing all relative paths found:
        if (!empty($conf['relPathPrefix'])) {
            $htmlParser = GeneralUtility::makeInstance(HtmlParser::class);
            $content = $htmlParser->prefixResourcePath($conf['relPathPrefix'], $content, $conf['relPathPrefix.']);
        }
        if ($content) {
            if (!isset($conf['nonCachedSubst'])) {
                $conf['nonCachedSubst'] = '';
            }
            $nonCachedSubst = isset($conf['nonCachedSubst.']) ? $this->cObj->stdWrap($conf['nonCachedSubst'], $conf['nonCachedSubst.']) : $conf['nonCachedSubst'];
            // NON-CACHED:
            if ($nonCachedSubst) {
                // Getting marks
                if (is_array($conf['marks.'])) {
                    foreach ($conf['marks.'] as $theKey => $theValue) {
                        if (!str_contains($theKey, '.')) {
                            $content = str_replace($PRE . $theKey . $POST, $this->cObj->cObjGetSingle($theValue, $conf['marks.'][$theKey . '.'] ?? [], 'marks.' . $theKey), $content);
                        }
                    }
                }
                // Getting subparts.
                if (is_array($conf['subparts.'])) {
                    foreach ($conf['subparts.'] as $theKey => $theValue) {
                        if (!str_contains($theKey, '.')) {
                            $subpart = $templateService->getSubpart($content, $PRE . $theKey . $POST);
                            if ($subpart) {
                                $this->cObj->setCurrentVal($subpart);
                                $content = $templateService->substituteSubpart($content, $PRE . $theKey . $POST, $this->cObj->cObjGetSingle($theValue, $conf['subparts.'][$theKey . '.'] ?? [], 'subparts.' . $theKey), true);
                            }
                        }
                    }
                }
                // Getting subpart wraps
                if (is_array($conf['wraps.'])) {
                    foreach ($conf['wraps.'] as $theKey => $theValue) {
                        if (!str_contains($theKey, '.')) {
                            $subpart = $templateService->getSubpart($content, $PRE . $theKey . $POST);
                            if ($subpart) {
                                $this->cObj->setCurrentVal($subpart);
                                $content = $templateService->substituteSubpart($content, $PRE . $theKey . $POST, explode('|', $this->cObj->cObjGetSingle($theValue, $conf['wraps.'][$theKey . '.'], 'wraps.' . $theKey)), true);
                            }
                        }
                    }
                }
            } else {
                // CACHED
                // Getting subparts.
                if (isset($conf['subparts.']) && is_array($conf['subparts.'])) {
                    foreach ($conf['subparts.'] as $theKey => $theValue) {
                        if (!str_contains($theKey, '.')) {
                            $subpart = $templateService->getSubpart($content, $PRE . $theKey . $POST);
                            if ($subpart) {
                                $this->getTypoScriptFrontendController()->register['SUBPART_' . $theKey] = $subpart;
                                $subparts[$theKey]['name'] = $theValue;
                                $subparts[$theKey]['conf'] = $conf['subparts.'][$theKey . '.'];
                            }
                        }
                    }
                }
                // Getting marks
                if (is_array($conf['marks.'])) {
                    foreach ($conf['marks.'] as $theKey => $theValue) {
                        if (!str_contains($theKey, '.')) {
                            $marks[$theKey]['name'] = $theValue;
                            $marks[$theKey]['conf'] = $conf['marks.'][$theKey . '.'];
                        }
                    }
                }
                // Getting subpart wraps
                if (isset($conf['wraps.']) && is_array($conf['wraps.'])) {
                    foreach ($conf['wraps.'] as $theKey => $theValue) {
                        if (!str_contains($theKey, '.')) {
                            $wraps[$theKey]['name'] = $theValue;
                            $wraps[$theKey]['conf'] = $conf['wraps.'][$theKey . '.'];
                        }
                    }
                }
                // Getting subparts
                $subpartArray = [];
                foreach ($subparts as $theKey => $theValue) {
                    // Set current with the content of the subpart...
                    $this->cObj->data[$this->cObj->currentValKey] = $this->getTypoScriptFrontendController()->register['SUBPART_' . $theKey];
                    // Get subpart cObject and substitute it!
                    $subpartArray[$PRE . $theKey . $POST] = $this->cObj->cObjGetSingle($theValue['name'], $theValue['conf'], 'subparts.' . $theKey);
                }
                // Reset current to empty
                $this->cObj->data[$this->cObj->currentValKey] = '';
                // Getting marks
                $markerArray = [];
                foreach ($marks as $theKey => $theValue) {
                    $markerArray[$PRE . $theKey . $POST] = $this->cObj->cObjGetSingle($theValue['name'], $theValue['conf'], 'marks.' . $theKey);
                }
                // Getting wraps
                $subpartWraps = [];
                foreach ($wraps as $theKey => $theValue) {
                    $subpartWraps[$PRE . $theKey . $POST] = explode('|', $this->cObj->cObjGetSingle($theValue['name'], $theValue['conf'], 'wraps.' . $theKey));
                }
                // Substitution
                if (!isset($conf['substMarksSeparately'])) {
                    $conf['substMarksSeparately'] = '';
                }
                $substMarksSeparately = isset($conf['substMarksSeparately.']) ? $this->cObj->stdWrap($conf['substMarksSeparately'], $conf['substMarksSeparately.']) : $conf['substMarksSeparately'];
                if ($substMarksSeparately) {
                    $content = $templateService->substituteMarkerArrayCached($content, [], $subpartArray, $subpartWraps);
                    $content = $templateService->substituteMarkerArray($content, $markerArray);
                } else {
                    $content = $templateService->substituteMarkerArrayCached($content, $markerArray, $subpartArray, $subpartWraps);
                }
            }
        }
        if (isset($conf['stdWrap.'])) {
            $content = $this->cObj->stdWrap($content, $conf['stdWrap.']);
        }
        return $content;
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }
}
