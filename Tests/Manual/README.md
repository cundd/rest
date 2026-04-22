# Manual tests

## Installation

1. Install TYPO3 and configure a website with some pages and a bit of content

2. Include the testing configuration

    ```yaml
    rest:
        settings:
            paths:
                georgringer-news:
                    path: georg_ringer-news-*
                    read: allow
                    write: allow

            aliases:
                customhandler: cundd-custom_rest-custom_handler

            languages:
                de-DE: 1
    ```

3. Install [news extension](https://typo3.org/extensions/repository/view/news)

4. Add a Frontend User with name `daniel` and API-key `api-key`

5. Add at least one news entry

6. Configure the alternative language `de-DE`
