##Usage

There are two use cases for this package. There is an abstract class `CsvUtility` that handles some general house keeping allowing for sub-classes to focus on specifics. The supported sub-class currently is `SilverStripeCsvUtility`.

**_Note:_** The values of `MyDataObject::getExportFields()` are parsed out as the header fields of the generated csv file.

**Generating a CSV file for response**

```php

<?php

class MyDataObject extends DataObjet implements Dynamic\CsvUtility\UtilInterface\CsvUtilityInterface
{
    
    private static $db = [
        'FirstName' => 'Varchar',
        'LastName' => 'Varchar',
    ];
    
    private static $has_one = [
    	'MyRelation' => 'MyOtherObject,
	 ];
    
    public function getExportFields() {
        return [
            'FirstName' => 'First Name',
            'LastName' => 'Last Name',
            'MyRelation.Title' => 'Relation Object Title',
        ];
    }
    
}

class MyOtherObject extends DataObject
{
	private static $db = [
		'Title' => 'varchar',
	];
	
	private static $has_many = [
		'MyDataObjects' => 'MyDataObject',
	];
}

class MyController extends Page_Controller
{
    
    public function getcsv(SS_HTTPRequest $request)
    {
        
		  new Dynamic\CsvUtility\Utility\SilverStripeCsvUtility(
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