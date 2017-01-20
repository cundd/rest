### Include this file to configure TYPO3 for the manual tests

<INCLUDE_TYPOSCRIPT: source="FILE:EXT:rest/Configuration/TypoScript/Content/setup.txt">
<INCLUDE_TYPOSCRIPT: source="FILE:EXT:rest/Configuration/TypoScript/Page/setup.txt">

plugin.tx_rest.settings {
    paths {
        virtualobject-content {
            path = VirtualObject-Content
            read = allow
            write = allow
        }
        virtualobject-page {
            path = VirtualObject-Page
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
