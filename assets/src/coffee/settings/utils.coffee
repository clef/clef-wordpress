(($, Backbone) ->

    class Utils

        @getErrorMessage: (data) ->
            if data.error
                return data.error
            else if data.data && data.data.error
                return data.data.error
            return data

    window.ClefUtils = Utils

).call(this, jQuery, Backbone)
