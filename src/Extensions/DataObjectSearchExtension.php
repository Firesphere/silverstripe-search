<?php

namespace Firesphere\SearchBackend\Extensions;

use Firesphere\SearchBackend\Models\DirtyClass;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Security\InheritedPermissionsExtension;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Class \Firesphere\SearchBackend\Extensions\DataObjectSearchExtension
 *
 * @property DataObject|DataObjectSearchExtension $owner
 */
class DataObjectSearchExtension extends DataExtension
{
    /**
     * @var array Cached permission list
     */
    public static $cachedClasses;
    /**
     * @var SiteConfig Current siteconfig
     */
    protected static $siteConfig;

    /**
     * Get the view status for each member in this object
     *
     * @return array
     */
    public function getViewStatus(): array
    {
        // return as early as possible
        /** @var DataObject|SiteTree $owner */
        $owner = $this->owner;
        if (isset(static::$cachedClasses[$owner->ClassName])) {
            return static::$cachedClasses[$owner->ClassName];
        }

        // Make sure the siteconfig is loaded
        if (!static::$siteConfig) {
            static::$siteConfig = SiteConfig::current_site_config();
        }
        // Return false if it's not allowed to show in search
        // The setting needs to be explicitly false, to avoid any possible collision
        // with objects not having the setting, thus being `null`
        // Return immediately if the owner has ShowInSearch not being `null`
        if ($owner->ShowInSearch === false || $owner->ShowInSearch === 0) {
            return ['false'];
        }

        $permissions = $this->getGroupViewPermissions($owner);

        if (!$owner->hasExtension(InheritedPermissionsExtension::class)) {
            static::$cachedClasses[$owner->ClassName] = $permissions;
        }

        return $permissions;
    }


    /**
     * Find or create a new DirtyClass for recording dirty IDs
     *
     * @param string $type
     * @return DirtyClass
     * @throws ValidationException
     */
    protected function getDirtyClass(string $type)
    {
        // Get the DirtyClass object for this item
        /** @var null|DirtyClass $record */
        $record = DirtyClass::get()->filter(['Class' => $this->owner->ClassName, 'Type' => $type])->first();
        if (!$record || !$record->exists()) {
            $record = DirtyClass::create([
                'Class' => $this->owner->ClassName,
                'Type'  => $type,
            ]);
            $record->write();
        }

        return $record;
    }

    /**
     * Determine the view permissions based on group settings
     *
     * @param DataObject|SiteTree|SiteConfig $owner
     * @return array
     */
    protected function getGroupViewPermissions($owner): array
    {
        // Switches are not ideal, but it's a lot more readable this way!
        switch ($owner->CanViewType) {
            case 'LoggedInUsers':
                $return = ['false', 'LoggedIn'];
                break;
            case 'OnlyTheseUsers':
                $return = ['false'];
                $return = array_merge($return, $owner->ViewerGroups()->column('Code'));
                break;
            case 'Inherit':
                $parent = !$owner->ParentID ? static::$siteConfig : $owner->Parent();
                $return = $this->getGroupViewPermissions($parent);
                break;
            case 'Anyone': // View is either not implemented, or it's "Anyone"
                $return = ['null'];
                break;
            default:
                // Default to "Anyone can view"
                $return = ['null'];
        }

        return $return;
    }

}