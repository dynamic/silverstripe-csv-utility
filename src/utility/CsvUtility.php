<?php

namespace dynamic\CsvUtility\Utility;

use \Injector;
use \DataObject;

/**
 * Class CsvUtility
 * @package dynamic\CsvUtility
 */
class CsvUtility
{

    /**
     * @var
     */
    private $raw_data;
    /**
     * @var
     */
    private $request;
    /**
     * @var array
     */
    private $allowed_report_types = [];
    /**
     * @var string
     */
    private $report_type;
    /**
     * @var array
     */
    private $pattern;
    /**
     * @var array
     */
    private $header_fields;
    /**
     * @var
     */
    private $handle;
    /**
     * @var
     */
    private $relation_name;
    /**
     * @var string
     */
    private $deliminator = ',';
    /**
     * @var string
     */
    private $enclosure = '"';

    /**
     * FACTCsvUtility constructor.
     * @param $data
     * @param $request
     */
    public function __construct($data, $request)
    {

        $this->setRawData($data);
        $this->setRequest($request);

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
     * @param $request
     * @return $this
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param array $types
     * @return $this
     */
    public function setAllowedReportTypes($types = [])
    {
        if ((array)$types === $types && !empty($types)) {
            $this->allowed_report_types = $types;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getAllowedReportTypes()
    {
        return $this->allowed_report_types;
    }

    /**
     * @return $this
     */
    public function setReportType()
    {
        if (!$request = $this->getRequest()) {
            user_error("The \$request isn't accessible or isn't set.", E_USER_ERROR);
        }
        if (!$reportRequestType = $request->getVar('ReportType')) {
            user_error("The request variable \"ReportType\" isn't accessible or isn't set.", E_USER_ERROR);
        }
        if (!array_key_exists($reportRequestType, $this->getAllowedReportTypes())) {
            user_error("The requested \"ReportType\" isn't an allowed report type.", E_USER_ERROR);
        }
        $this->report_type = $reportRequestType;
        return $this;
    }

    /**
     * @return string
     */
    public function getReportType()
    {
        if (!$this->report_type) {
            $this->setReportType();
        }
        return $this->report_type;
    }

    /**
     * @return $this
     */
    public function setPattern()
    {
        $reportType = $this->getReportType();
        $class = $this->getAllowedReportTypes()[$reportType];
        $extensions = Injector::inst()->create($class)->getExtensionInstances();
        $extensions[$class] = $class;

        if (!$this->getImplementsUtilInterface($extensions)) {
            user_error("Class {$class} is required to implement dynamic\\CsvUtility\\UtilInterface\\CsvUtilityInterface before a report can be generated.", E_USER_ERROR);
        }
        $this->pattern = Injector::inst()->create($class)->getExportFields();

        return $this;
    }

    /**
     * @param array $extensions
     * @return bool
     */
    public function getImplementsUtilInterface($extensions = [])
    {
        $implements = false;
        foreach ($extensions as $key => $val) {
            if (!$implements) {
                if (in_array('dynamic\CsvUtility\UtilInterface\CsvUtilityInterface', class_implements($key))) $implements = true;
            }
        }
        return $implements;
    }

    /**
     * @return array
     */
    public function getPattern()
    {
        if (!$this->pattern) {
            $this->setPattern();
        }
        return $this->pattern;
    }

    /**
     * @return $this
     */
    public function setHeaderFields()
    {
        $this->header_fields = array_values($this->getPattern());
        return $this;
    }

    /**
     * @return array
     */
    public function getHeaderFields()
    {
        if (!$this->header_fields) {
            $this->setHeaderFields();
        }
        return $this->header_fields;
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
     * @param $relationName
     * @return $this
     */
    public function setRelationName($relationName)
    {
        $this->relation_name = $relationName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRelationName()
    {
        return $this->relation_name;
    }

    /**
     * @param $row
     * @return $this
     */
    protected function addFileContents($row)
    {
        $deliminator = $this->getDeliminator();
        $enclosure = $this->getEnclosure();
        $relationName = $this->getRelationName();

        $data = [];
        if ((array)$row === $row) {
            //todo generate new row from pattern and provided row data
        } elseif ($row instanceof DataObject) {
            $pattern = $this->getPattern();

            foreach ($pattern as $key => $val) {
                if (strpos($key, '.') && $relationName && is_string($relationName)) {
                    $parts = explode('.', $key);
                    if (count($parts) == 3) {
                        $value = ($parts[0] == $relationName) ? $row->$relationName()->$parts[1]()->$parts[2] : $row->$parts[0]()->$parts[1]()->$parts[2];
                    } else {
                        $value = ($parts[0] == $relationName) ? $row->$relationName()->$parts[1] : $row->$parts[0]()->$parts[1];
                    }
                } else {
                    $value = $row->$key;
                }
                $data[] = $value;
            }
        }

        $this->putCSV($this->getHandle(), $data, $deliminator, $enclosure);

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

    /**
     * @return string
     */
    protected function generateFile()
    {
        $contents = '';
        $data = $this->getRawData();

        $this->putCSV($this->getHandle(), $this->getHeaderFields());

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
     * @return string
     */
    public function getFileContents()
    {
        return $this->generateFile();
    }

}