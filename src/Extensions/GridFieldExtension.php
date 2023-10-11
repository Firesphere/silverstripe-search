<?php
/**
 * class GridFieldExtension|Firesphere\SearchBackend\Extensions\GridFieldExtension Add colours to the GridField
 *
 * @package Firesphere\Search\Backend
 * @author Simon `Firesphere` Erkelens; Marco `Sheepy` Hermo
 * @copyright Copyright (c) 2018 - now() Firesphere & Sheepy
 */

namespace Firesphere\SearchBackend\Extensions;

use Firesphere\SearchBackend\Models\SearchLog;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\ORM\DataObject;
use SilverStripe\View\ViewableData;

/**
 * Class GridFieldExtension
 * Dirty hack to get the alert/warning/info classes in to the gridfield
 *
 * @property GridField|GridFieldExtension $owner
 */
class GridFieldExtension extends Extension
{
    /**
     * Add the visibility classes to the GridField
     *
     * @param array $classes
     * @param int $total
     * @param string $index
     * @param DataObject $record
     */
    public function updateNewRowClasses(array &$classes, int $total, string $index, ViewableData $record)
    {
        if ($record instanceof SearchLog) {
            $classes[] = $record->getExtraClass();
        }
    }
}
