tinymce.PluginManager.add('atkp_button_picker', function (editor, url) {
    editor.addButton('atkp_button_picker', {
        name: 'atkp_button_picker',
        classes: 'widget btn atkp_button_picker',
        tooltip: 'affiliate-toolkit Shortcodes',
        text: 'AT Shortcode',
        icon: false
    });

    editor.addCommand('InsertATShortcode', function () {
        //editor.execCommand('mceInsertContent', false, '[atkp][/atkp]');

        var generator_button = jQuery('.atkp-generator-button');
        generator_button.trigger("click");

    });

    editor.addMenuItem('atkp_button_picker', {
        icon: 'hr',
        text: 'AT Shortcode',
        cmd: 'InsertATShortcode',
        context: 'insert'
    });

});
