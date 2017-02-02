### Include this file to configure TYPO3 for the manual tests

<INCLUDE_TYPOSCRIPT: source="FILE:EXT:rest/Configuration/TypoScript/Content/setup.txt">
<INCLUDE_TYPOSCRIPT: source="FILE:EXT:rest/Configuration/TypoScript/Page/setup.txt">

plugin.tx_rest.settings {
    paths {
        virtual_object-content {
            path = virtual_object-content
            read = allow
            write = allow
        }
        virtual_object-page {
            path = virtual_object-page
            read = allow
            write = allow
        }

        cundd-custom_rest-route {
            path = cundd-custom_rest-route
            read = allow
            write = allow
        }
        cundd-custom_rest-require {
            path = cundd-custom_rest-require
            read = require
            write = require
        }

        georgringer-news {
            path = georg_ringer-news-*
            read = allow
            write = allow
        }
    }

    aliases {
        customhandler = cundd-custom_rest-custom_handler
    }
}
