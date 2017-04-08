Manual tests
============

Installation
------------

1. Install TYPO3 and configure a website with some pages and a bit of content

2. Include the testing TypoScript configuration 
    ```TypoScript
    <INCLUDE_TYPOSCRIPT: source="FILE:EXT:rest/Tests/Configuration/TypoScript/Configuration.ts">
    ```

3. Install [news extension](https://typo3.org/extensions/repository/view/news)

4. Install [custom_rest extension](https://github.com/cundd/custom_rest)

5. Add a Frontend User with name `daniel` and API-key `api-key`
