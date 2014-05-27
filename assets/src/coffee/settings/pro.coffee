(($, Backbone) ->

    ProView = Backbone.View.extend
        el: '#clef-pro-section'
        getServicesURL: ajaxurl + '?action=clef_get_pro_services'
        subViews: []
        initialize: (@opts, @model) ->
            $.getJSON @getServicesURL, { _wpnonce: @opts.nonces.getProServices }
                .success (data) =>
                    @servicesAvailable = data
                    if 'customize' in @servicesAvailable
                        @customizer = new CustomizationView(@opts, @model)
                        @subViews.push @customizer
                    @render()
                .fail (res) -> console.log res.responseText
        render: ->
            for view in @subViews
                view.render()

            @$el.show()

    CustomizationView = Backbone.View.extend
        el: '#clef-pro-customization'
        events:
            'click #clef-custom-logo-upload': 'openMediaUploader'
            'click #clef-custom-logo-clear': 'clearLogo'
            'change input, change textarea': 'render'
            'keyup textarea': 'render'
        initialize: (@opts, @model) ->
            @preview = _.template($('#clef-customization-template').html())
        render: ->
            @$el.find('#custom-login-view')
                .html @preview
                    image: @image()
                    message: @message()

            @$el.find('#clef-custom-logo-clear').toggle !!@image()
            @$el.show()
        openMediaUploader: ->
            if @uploader
                @uploader.open()
                return

            @uploader = wp.media.frames.file_frame =  wp.media
                title: 'Choose an image'
                button:
                    text: 'Choose an image'
                multiple: false

            @uploader.on 'select', =>
                attachment = @uploader.state().get('selection').first().toJSON()
                @model.save
                    'wpclef[customization_logo]': attachment.url
                @render()

            @uploader.open()
        clearLogo: ->
            @model.save
                'wpclef[customization_logo]': ''
            @render()
        image: ->
            @model.cget 'customization_logo'
        message: ->
            @$el.find('textarea').val()

    window.ClefProView = ProView

).call(this, jQuery, Backbone)
