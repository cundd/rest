Installation
============

TYPO3 Extension Repository
--------------------------

The extension can be installed from within the Extension Manager and can be found 
[here](https://extensions.typo3.org/extension/rest/) in the TYPO3 extension repository.


Composer
--------

If you use TYPO3 in Composer mode use

```bash
composer require cundd/rest
```


Latest source
-------------

1. Clone the REST extension from [source](https://github.com/cundd/rest.git)
```bash
cd typo3conf/ext/;
git clone https://github.com/cundd/rest.git;
```
2. Install REST in the Extension Manager
3. Configure the API access ([learn more](/Configuration/Basic/))
4. Connect to `your-domain.com/rest/` (`rest/` is the request namespace)
