<?php

namespace Dynamic\CsvUtility\Utility;

use Dynamic\CsvUtility\CsvUtilTraits\CsvUtilityTrait;

/**
 * Class CsvUtility
 * @package Dynamic\CsvUtility\Utility
 */
abstract class CsvUtility
{

    use CsvUtilityTrait;

    /**
     * @var
     */
    private $raw_data;
    /**
     * @var
     */
    private $handle;
    /**
     * @var array
     */
    protected $header_fields = [];
    /**
     * @var string
     */
    private $deliminator = ',';
    /**
     * @var string
     */
    private $enclosure = '"';

    /**
     * CsvUtility constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->setRawData($data);
    }

    /**
     * @param $data
     * @return $this
     */
    public function setRawData($data)
    {
        $this->raw_data = $data;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRawData()
    {
        return $this->raw_data;
    }


    /**
     *
     */
    public function setHandle()
    {
        $this->handle = fopen('php://temp', 'r+');
    }

    /**
     * @return mixed
     */
    protected function getHandle()
    {
        if (!$this->handle) {
            $this->setHandle();
        }
        return $this->handle;
    }

    /**
     * @param $deliminator
     * @return $this
     */
    public function setDeliminator($deliminator)
    {
        $this->deliminator = $deliminator;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDeliminator()
    {
        return $this->deliminator;
    }

    /**
     * @param $enclosure
     * @return $this
     */
    public function setEnclosure($enclosure)
    {
        $this->enclosure = $enclosure;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEnclosure()
    {
        return $this->enclosure;
    }

    /**
     * @param array $headerFields
     * @return $this
     */
    public function setHeaderFields($headerFields)
    {
        if ((array)$headerFields !== $headerFields) {

        }
        $this->header_fields = $headerFields;
        return $this;
    }

    /**
     * @return array
     */
    public function getHeaderFields()
    {
        return $this->header_fields;
    }

    /**
     * @return string
     */
    public function getFileContents()
    {
        return $this->generateFileData();
    }

    /**
     * @return string
     */
    protected function generateFileData()
    {
        $contents = '';
        $data = $this->getRawData();

        if (!empty($this->getHeaderFields())) {
            $this->putCSV($this->getHandle(), $this->getHeaderFields());
        }

        foreach ($data as $d) {
            $this->addFileContents($d);
        }
        $handle = $this->getHandle();
        rewind($handle);

        while (!feof($this->getHandle())) {
            $contents .= fread($handle, 8192);
        }

        fclose($handle);
        return $contents;
    }

    /**
     * @param array $row
     * @return $this
     */
    protected function addFileContents($row = [])
    {
        $deliminator = $this->getDeliminator();
        $enclosure = $this->getEnclosure();

        $row = $this->preProcessData($row);

        $data = [];
        if ((array)$row === $row) {

            $addRowData = function ($key, $val) use (&$data, &$deliminator, &$enclosure) {
                $data[] = $val;
            };

            foreach ($row as $key => $val) {
                $addRowData($key, $val);
            }

            $this->putCSV($this->getHandle(), $data, $deliminator, $enclosure);

        }

        return $this;
    }

    /**
     * @param $handle
     * @param $data
     * @param string $deliminator
     * @param string $enclosure
     */
    protected function putCSV($handle, $data, $deliminator = ',', $enclosure = '"')
    {
        fputcsv($handle, $data, $deliminator, $enclosure);
    }


}