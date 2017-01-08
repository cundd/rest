### Include this file to configure TYPO3 for the manual tests

<INCLUDE_TYPOSCRIPT: source="FILE:EXT:rest/Configuration/TypoScript/Content/setup.txt">
<INCLUDE_TYPOSCRIPT: source="FILE:EXT:rest/Configuration/TypoScript/Page/setup.txt">

plugin.tx_rest.settings {
    paths {
        2010 {
            path = VirtualObject-Content
            read = allow
            write = allow
        }
        2015 {
            path = VirtualObject-Page
            read = allow
            write = allow
        }

        400 {
            path = cundd-custom_rest-route
            read = allow
            write = allow
        }
        401 {
            path = cundd-custom_rest-require
            read = require
            write = require
        }

        3 {
            path = GeorgRinger-News-*
            read = allow
            write = allow
        }
    }

    aliases {
        customhandler = cundd-custom_rest-custom_handler
    }
}
