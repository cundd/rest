# REST 4.0.0

After over a year of work the fourth major release is released. The main focus of this iteration is to support TYPO3 v9 
LTS and to clean up the [`DataProvider's` interface](Classes/DataProvider/DataProviderInterface.php). 

> If an installation uses a custom Data Provider, it must be adapted to the new interface.

A collection of the most important changes can be found below.

## Breaking changes
- Prepend the name of custom HTTP headers with "X-" ([#63e0082](https://github.com/cundd/rest/commit/63e0082))
- Languages are used for Domain Models and must be mapped in TypoScript ([#6848078](https://github.com/cundd/rest/commit/6848078), [FAQs](https://rest.corn.rest/FAQ/#internationalization-and-localization))
- Requested language has precedence over Site language ([#3703e59](https://github.com/cundd/rest/commit/3703e59))
- Clean up the Object Manager ([#fab4139](https://github.com/cundd/rest/commit/fab4139))
- Fix Dispatcher entry point and add return type (Fix #58, [#064a641](https://github.com/cundd/rest/commit/064a641))
- Replace headers configured in `settings.responseHeaders` ([#3589149](https://github.com/cundd/rest/commit/3589149))
- Add scalar types for the CRUD Handler ([#2a0ea1b](https://github.com/cundd/rest/commit/2a0ea1b))
- Do not call `update()` if `__identity` is given ([#ca2657a](https://github.com/cundd/rest/commit/ca2657a))
- Drop TYPO3 7 LTS Support ([#82c4fa2](https://github.com/cundd/rest/commit/82c4fa2))
- Convert the incoming property parameter into a valid property key (changes to `DataProviderInterface::getModelProperty()`, [#53ce98c](https://github.com/cundd/rest/commit/53ce98c))
- Prefer to call a property getter method over calling `_getProperty()` (changes to `DataProviderInterface::getModelProperty()`, [#53ce98c](https://github.com/cundd/rest/commit/53ce98c))
- Add CORS headers to failed requests ([#ba3f86c](https://github.com/cundd/rest/commit/ba3f86c))
- Require PHP 7.1 or later ([#bd043c7](https://github.com/cundd/rest/commit/bd043c7))
- Virtual Object Persistence Manager and Backend require a Query instance as argument ([#52dca42](https://github.com/cundd/rest/commit/52dca42), [#dac8157](https://github.com/cundd/rest/commit/dac8157))
- Remove deprecated `getCacheLiveTime()` ([#a901e2e](https://github.com/cundd/rest/commit/a901e2e))
- Remove deprecated methods ([#cc0e58a](https://github.com/cundd/rest/commit/cc0e58a))

## Features and improvements
- Add support for new Site Handling and improve language handling ([#aa1404e](https://github.com/cundd/rest/commit/aa1404e), [#61eb3fd](https://github.com/cundd/rest/commit/61eb3fd), [#aa1404e](https://github.com/cundd/rest/commit/aa1404e))
- Add `cors.allowedOrigins` to define a list of allowed CORS origins ([#b8a9e80](https://github.com/cundd/rest/commit/b8a9e80))
- Add Parameter Type `{raw}` (Fix #56, [#3047fad](https://github.com/cundd/rest/commit/3047fad))
- Add additional debugging headers ([#b461372](https://github.com/cundd/rest/commit/b461372), [#ab26785](https://github.com/cundd/rest/commit/ab26785))
- Update the icons [!new icon](https://rest.corn.rest/Resources/Public/Images/rest-logo-512-solid.png) ([#eaf54d5](https://github.com/cundd/rest/commit/eaf54d5))
- Add route for OPTIONS requests to the Auth Handler ([#6fcda9a](https://github.com/cundd/rest/commit/6fcda9a))
- Replace some `@inject` annotations with constructor or method injection ([#94780af](https://github.com/cundd/rest/commit/94780af))
- Add the Resource Type to the cache tags ([#c17a736](https://github.com/cundd/rest/commit/c17a736))
- Add scalar type checks ([#23595da](https://github.com/cundd/rest/commit/23595da), [#e16bbba](https://github.com/cundd/rest/commit/e16bbba), [#1dab787](https://github.com/cundd/rest/commit/1dab787), [#7d1648b](https://github.com/cundd/rest/commit/7d1648b))
- Add factory methods for `PATCH` and `OPTIONS` requests ([#b151929](https://github.com/cundd/rest/commit/b151929))
- Respect the Cache Lifetime from the Resource Type configuration and use "lifetime" in names ([#68b0099](https://github.com/cundd/rest/commit/68b0099))
- Change the "camelCase to underscore" method for `normalizeResourceType()` ([#556d3ff](https://github.com/cundd/rest/commit/556d3ff))
- Add support to limit the number of results in `listAll()` for custom extensions ([#a4da600](https://github.com/cundd/rest/commit/a4da600))


### Data Provider
- Cleanup the Data Provider and CRUD Handler APIs ([#f808610](https://github.com/cundd/rest/commit/f808610), [#9027618](https://github.com/cundd/rest/commit/9027618))
- Add `isModelNew()` to allow overriding the check for new instances ([#070d375](https://github.com/cundd/rest/commit/070d375))
- Add `withResourceType()` and strict checks for the Format ([#fe9b05c](https://github.com/cundd/rest/commit/fe9b05c))
- Add `countAllModels()` to Data Providers ([#98b8cdb](https://github.com/cundd/rest/commit/98b8cdb))
- Prioritize `Vendor\Namespace` classes ([#8ffdb9a](https://github.com/cundd/rest/commit/8ffdb9a))

### Object Manager
- Also detect interfaces in `getImplementationFromResourceConfiguration()` ([#1f58306](https://github.com/cundd/rest/commit/1f58306))
- Add support to specify the DataProvider in TypoScript ([#7e4c93a](https://github.com/cundd/rest/commit/7e4c93a))
- Decouple Object Manager from TYPO3 ([#fe3e106](https://github.com/cundd/rest/commit/fe3e106), [#926b94f](https://github.com/cundd/rest/commit/926b94f))

### Virtual Objects
- Add support for `Constraint` objects ([#cf5d6b3](https://github.com/cundd/rest/commit/cf5d6b3))
- Add scalar types for `WhereClauseBuilder` method arguments ([#a915832](https://github.com/cundd/rest/commit/a915832))
- Move the Operator constants into a separate interface ([#a947b2c](https://github.com/cundd/rest/commit/a947b2c))
- Add constructor for Query ([#2d4a403](https://github.com/cundd/rest/commit/2d4a403))
- Add SQL Expression object ([#5f156f5](https://github.com/cundd/rest/commit/5f156f5))
- Enhance the possibilities for subclasses of the Where Clause Builder ([#58997ae](https://github.com/cundd/rest/commit/58997ae))

