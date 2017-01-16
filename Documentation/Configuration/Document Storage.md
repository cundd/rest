Document Storage
================

The Document Storage is an *experimental* store for objects of class `\Cundd\Rest\Domain\Model\Document`. 


Database
--------

The `database` is the name of a collection of Documents. It has to be an alphanumeric, lowercase string. This string is saved with each Document and enables you to list, count, or remove all Documents of the given database.


Document
--------

The Document class is a flexible, schema-less object. It's (required) core properties are an ID and the name of the connected `database`. All other properties can be dynamically set and retrieved through key-value-coding methods:

Get the value for a key:

```php
$document->valueForKey($key);
```

Get the value for a key path (i.e. "foo.bar"):

```php
$document->valueForKeyPath($keyPath);
```


Set the value for a key:

```php
$document->setValueForKey($key, $value);
```
	
	
### ID

The object's ID is it's unique identifier inside the database.

Setting the ID:

```php
$document->setId($id);
```


Retrieving the ID:

```php
$document->getId();
```


### Database

The accessor methods for the database property contain a leading underscore to prevent them from accidentally being overwritten in subclasses

Setting the database:

```php
$document->_setDb(alphanumericLowercaseDatabaseName);
```

Retrieving the database:
	
```php
$document->_getDb();
```
	

### GUID

A Document's global unique identifier (GUID) can only exist once in the whole system. It is a combination of the Document's database and it's ID 

```php
public function getGuid() {
	$guid = $this->db . '-' . $this->id;
	return $guid !== '-' ? $guid : NULL;
}
```
	

There is no setter method for the GUID, but it can be retrieved through:

```php
$document->getGuid();
```


Repository
----------

The documents are collected in the DocumentRepository (`\Cundd\Rest\Domain\Repository\DocumentRepository`) which can be used much like other Extbase repositories. Nevertheless you have to keep some things in mind:


### Setting the database

When managing Documents the repository has to know which database the current Document belongs to. You can provide this information either by setting the current database in the repository or by setting a single Document's database.

Setting the repository's database:

```php
$documentRepository->setDatabase($alphanumericLowercaseDatabaseName);
```


Setting the Document's database (as seen above):

```php
$aDocument->_setDb(alphanumericLowercaseDatabaseName);
```

	
If a Document has the database set, this will be used instead of the repository's current database. If the Document's database isn't already set, the repository takes it's current database and copies it to the Document.

### Finding Documents

Find an object by it's GUID:

```php
$documentRepository->findByGuid($guid);
```


Find all Documents in one database:

```php
$documentRepository->setDatabase($database);
$documentRepository->findAll();
```


Search for Documents matching the given dictionary (associative array) of property keys and values:

```php
$documentRepository->findWithProperties($properties, $count, $limit);
```


Find all Documents ignoring their database:

```php
$documentRepository->findAllIgnoreDatabase();
```


### Managing Documents

Adding an object:

```php
$documentRepository->add($object);
```


Updating an object:

```php
$documentRepository->update($modifiedObject);
```


Removing an object:

```php	
$documentRepository->remove($object);
```

	
Remove all Documents from one database:

```php	
$documentRepository->removeAllFromDatabase($database);
```


The register() method is a shorthand for: Object exists? Yes -> update / No -> add

```php
$documentRepository->register($data);
```

