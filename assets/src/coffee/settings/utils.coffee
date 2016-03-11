(($, Backbone) ->

    class Utils

        @getErrorMessage: (data) ->
            if typeof data == "string"
                try
                    data = $.parseJSON(data)
                catch

            if data.error
                return data.error
            else if data.data && data.data.error
                return data.data.error
            return data

        @getURLParams: ->
            query = window.location.search.substring(1)
            raw_vars = query.split("&")

            params = {}

            for v in raw_vars
                [key, val] = v.split("=")
                params[key] = decodeURIComponent(val)

            params

    window.ClefUtils = Utils

).call(this, jQuery, Backbone)
