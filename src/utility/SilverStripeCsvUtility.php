<?php

namespace Dynamic\CsvUtility\Utility;

use \DataObject;
use \Injector;

/**
 * Class SilverStripeCsvUtility
 */
class SilverStripeCsvUtility extends CsvUtility
{

    /**
     * @var string
     */
    private $relation_name;
    /**
     * @var string
     */
    private $data_object_class;
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
     * @var string
     */
    private $utility_interface = 'Dynamic\CsvUtility\UtilInterface\CsvUtilityInterface';

    public function __construct($data, $request)
    {
        parent::__construct($data);
        $this->setRequest($request);

    }

    /**
     * Override CsvUtility::getHeaderFields() to generate header fields based on the model's declaration
     *
     * @return $this
     */
    public function getHeaderFields()
    {
        return array_values($this->getPattern());
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
     * @param string $dataObjectClass
     * @return $this
     */
    public function setDataObjectClass($dataObjectClass = '')
    {
        if ($dataObjectClass === '' || !is_string($dataObjectClass)) {
            $reportType = $this->getReportType();
            $dataObjectClass = $this->getAllowedReportTypes()[$reportType];
        }
        $this->data_object_class = $dataObjectClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getDataObjectClass()
    {
        if (!$this->data_object_class) {
            $this->setDataObjectClass();
        }
        return $this->data_object_class;
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
            user_error("The requested report type \"{$reportRequestType}\" isn't allowed.", E_USER_ERROR);
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
     * @param $interface
     * @return $this
     */
    public function setUtilityInterface($interface)
    {
        $this->utility_interface = $interface;
        return $this;
    }

    /**
     * @return string
     */
    public function getUtilityInterface()
    {
        return $this->utility_interface;
    }

    /**
     * @return $this
     */
    public function setPattern()
    {
        $class = $this->getDataObjectClass();
        $extensions = Injector::inst()->create($class)->getExtensionInstances();
        $extensions[$class] = $class;

        if (!$this->getImplementsUtilInterface($extensions)) {
            user_error("Class {$class} is required to implement {$this->getUtilityInterface()} before a report can be generated.", E_USER_ERROR);
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
                if (in_array($this->getUtilityInterface(), class_implements($key))) $implements = true;
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
     * Allow for traversing a single has_one relation level through dot notation
     *
     * @param $data
     * @return array
     */
    public function preProcessData($data)
    {
        $data = parent::preProcessData($data);
        $arrayData = [];

        $pattern = $this->getPattern();

        $relationName = $this->getRelationName();

        if ($data instanceof DataObject) {
            $addToArrayData = function ($key, $val) use (&$data, &$relationName, &$arrayData) {
                if (strpos($key, '.') && $relationName && is_string($relationName)) {
                    $parts = explode('.', $key);
                    if (count($parts) == 3) {
                        $value = ($parts[0] == $relationName) ? $data->$relationName()->$parts[1]()->$parts[2] : $data->$parts[0]()->$parts[1]()->$parts[2];
                    } else {
                        $value = ($parts[0] == $relationName) ? $data->$relationName()->$parts[1] : $data->$parts[0]()->$parts[1];
                    }
                } else {
                    $value = $data->$key;
                }
                $arrayData[] = $value;
            };

            foreach ($pattern as $key => $val) {
                $addToArrayData($key, $val);
            }
        }

        return $arrayData;
    }

}