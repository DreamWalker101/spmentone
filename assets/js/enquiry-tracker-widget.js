
        initializejQuery();

        function initializejQuery() {
            if (window.jQuery === undefined) {
                injectScript(getProtocol() + 'code.jquery.com/jquery-3.1.0.min.js', main);
            } else {
                jQuery = window.jQuery;
                main();

            }
        }
        function getProtocol() {
            return ('https:' == document.location.protocol ? 'https://' : 'http://');
        }
        function injectScript(src, cb) {
            var sj = document.createElement('script');
            sj.type = 'text/javascript';
            sj.async = true;
            sj.src = src;
            sj.addEventListener ? sj.addEventListener('load', cb, false) : sj.attachEvent('onload', cb);
            var s = document.getElementsByTagName('script')[0];
            s.parentNode.insertBefore(sj, s);
        }

        function main() {
            var idOnPage = 0;
            jQuery(".et-widget").each(function(index,value) {
                idOnPage++;
                var widgetType = +getHtmlPropertyETWidget(this, 'widget-type', "data-widget-type");
                var widgetId = +getHtmlPropertyETWidget(this, 'widget-id', "data-widget-id");
                var formId = getHtmlPropertyETWidget(this, 'form-id', "data-form-id");
                var widgetPropertiesArr = 'data-widget-event-type,data-widget-campus-code,data-widget-event-id'.split(',');
                var parameters = {};
                // scan for all of the defined WidgetDataProperties in the .et-widget div.
                widgetPropertiesArr.forEach((key) => {
                    var value = getHtmlPropertyETWidget(this, key);
                    if (value) { parameters[key] = value; };
                });
                url = 'release' == 'debug' ? 'http://localhost:4201': 'https://app.enquirytracker.net';
                const isIosMobile = navigator.userAgent.indexOf("iPhone") != -1 || navigator.userAgent.indexOf("iPod") != -1 || navigator.userAgent.indexOf("iPad") != -1;
                const isIosBinary = isIosMobile ? 1 : 0;
                url = url + "/api/noAuth/widget/getWidget/a7be75e7a05e4a05a1591215b7a94cd7/" + widgetType + "/" + widgetId + "/" + formId + "/" + isIosBinary + "/" +
                       idOnPage + "?dataAttributes=" + encodeURIComponent(JSON.stringify(parameters));
                var d = jQuery(this);
                jQuery.ajax({
                    url: url,
                    method: "GET",
                    headers: {
                        Accept: 'text/html',
                    },
                    success: function(o) {
                        console.log("successfully got widget");
                        d.html(o);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log(textStatus, errorThrown);
                    }
                });
            })
        }

        function getHtmlPropertyETWidget(self, ...propertyNames)
        {
            var temp = propertyNames.reduce((prev, current) => {
                return prev ? prev: jQuery(self).attr(current);
            }, "");
            return temp;
        }
        