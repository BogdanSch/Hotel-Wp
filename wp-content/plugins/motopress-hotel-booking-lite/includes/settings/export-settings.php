<?php

namespace MPHB\Settings;

use MPHB\Bundles\ExportBundle;

/**
 * @since 3.5.0
 */
class ExportSettings
{
    /**
     * @var \MPHB\Bundles\ExportBundle|null Null till the first call of
     *      getBundle().
     */
    protected $exportBundle = null;

    /**
     * @param mixed $defaultValue
     * @return mixed
     */
    public function getUserExportColumns($defaultValue = array())
    {
        $columns = get_user_meta(get_current_user_id(), 'mphb_export_columns', true);

        if (!is_array($columns)) {
            return $defaultValue;
        } else {
            return $columns;
        }
    }

    /**
     * @param array $columns
     */
    public function setUserExportColumns($columns)
    {
        $oldValue = $this->getUserExportColumns('');
        update_user_meta(get_current_user_id(), 'mphb_export_columns', $columns, $oldValue);
    }

    /**
     * @return \MPHB\Bundles\ExportBundle
     */
    public function getBundle()
    {
        if (is_null($this->exportBundle)) {
            $this->exportBundle = new ExportBundle();
        }

        return $this->exportBundle;
    }
}
