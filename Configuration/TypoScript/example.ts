# Sitemap configuration
plugin.tx_twsitemap.settings.entries {
    0 {
        pid = 1
        #        languages = 0 // Enable to submit a language parameter during the sitemap generation
        locales = en
        domain = example.com
        origin = sitemap
        changefreq = daily
        priority = 0.7
        entries = typoscript
        entries {
            10 = HMENU
            10 {
                1 = TMENU
                1 {
                    expAll = 1
                    noBlur = 1
                    wrap = |
                    NO {
                        doNotShowLink = 1
                        stdWrap2 {
                            if {
                                isFalse {
                                    field = tx_twsitemap_noindex
                                }
                            }

                            cObject = CASE
                            cObject {
                                key.field = doktype
                                1 = COA
                                1 {
                                    10 = TEXT
                                    10 {
                                        wrap = <a href="|"
                                        typolink {
                                            parameter.field = uid
                                            forceAbsoluteUrl = 1
                                            returnLast = url
                                        }

                                        htmlSpecialChars = 1
                                    }

                                    20 = TEXT
                                    20.field = SYS_LASTCHANGED
                                    20.ifEmpty.field = crdate
                                    20.noTrimWrap = | data-lastmod="|"|

                                    30 = TEXT
                                    30.field = uid
                                    30.noTrimWrap = | data-source="|"/>|
                                }

                                default = TEXT
                            }
                        }
                    }
                }

                2 < .1
                3 < .1
                4 < .1
            }
        }
    }
}
