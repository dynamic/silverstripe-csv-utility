# csv-utility

A csv utility class that aids in converting DataLists and ArrayLists to csv data.

## Requirements

See the [composer.json](composer.json) file.

## Installation

`composer require dynamic/csv-utility`

## Example usage

```php

<?php

class MyDataObject extends DataObjet implements CsvUtilityInterface
{
    
    private static $db = [
        'FirstName' => 'Varchar',
        'LastName' => 'Varchar',
    ];
    
    public function getExportFields() {
        return [
            'FirstName' => 'First Name',
            'LastName' => 'Last Name',
        ];
    }
    
}

class MyController extends Page_Controller
{
    
    public function getcsv(SS_HTTPRequest $request)
    {
        
        $utility = new dynamic\CsvUtility\CsvUtility(
            MyDataObject::get()
                ->filter([
                    'Foo' => 'a',
                    'Bar' => 'b',
                ]), $request
        );
        
        $utility->setAllowedReportTypes([
            'MyRequestVarToCheckFor' => 'MyDataObject',
        ]);
        
        $csv = $utility->getFileContents();
        
        if ($csv) {
            return SS_HTTPRequest::send_file($csv, $fileName = 'MyFileName.csv', 'Content-Type: text/csv');
        }
        
        return $this->httpError(404);
        
    }

}


```