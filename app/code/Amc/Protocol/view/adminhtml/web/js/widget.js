require([
    "jquery",
    'mage/template',
    'jquery/ui',
    'keynavigator'
], function($)
{
    $(function()
    {
        $('a[data-selector="dmitro"]').click(function(e) {
            e.preventDefault();
            var wrapper = $('#protocol-dialog').length > 0
                ? $('#protocol-dialog')
                : $('<div id="protocol-dialog"/>').appendTo('body');

            wrapper.dialog({
                title: 'Protocol',
                width: '75%',
                minHeight: 650,
                modal: true,
                resizable: false,
                position: {
                    my: 'left top',
                    at: 'center top',
                    of: 'body'
                },
                open: function () {
                    $(this).closest('.ui-dialog').addClass('ui-dialog-active');
                    var topMargin = $(this).closest('.ui-dialog').children('.ui-dialog-titlebar').outerHeight() + 45;
                    $(this).closest('.ui-dialog').css('margin-top', topMargin);
                    $(this).html($('.loading-mask').show());
                    console.log($);
//                    $('#protocol-dialog').load('/admin/protocol/index/load/', {protocol_id: 123}, function(response) {
//
//                    });
                    $.get('/admin/protocol/index/load/', {protocol_id: 2}, function(response) {
                        $('#protocol-dialog').html(response);
                    });
                },
                close: function () {
                    $(this).closest('.ui-dialog').removeClass('ui-dialog-active');
                }
            });

            wrapper.on('dialogclose', function () {
            });

        });
    });
});

