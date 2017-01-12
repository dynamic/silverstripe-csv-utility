<?php

namespace Dynamic\CsvUtility\UtilInterface;

/**
 * Interface CsvUtilityInterface
 * @package Dynamic\CsvUtility\UtilInterface
 */
interface CsvUtilityInterface
{
    /**
     * Return a map of Field names and readable titles for an exported csv file
     * array(
     *   'FirstName' => 'First Name',
     *   'Surname' => 'Last Name',
     *   'Address' => 'Home Address',
     * );
     */
    public function getExportFields();

}