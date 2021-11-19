jQuery(function ($) {
$(document).ready(function () {
    [].slice.call(document.querySelectorAll('.woo-chatbot-tabs')).forEach(function (el) {
        new CBPFWTabs(el);
    });



    $(document).on('change','#qcld_woo_chatbot_change_bg',function (e) {

         if($(this).is(':checked')){

             $('.qcld-woo-chatbot-board-bg-container').show();

         }else{

             $('.qcld-woo-chatbot-board-bg-container').hide();

         }

    });


        //Custom Backgroud image

        $('.qcld_woo_chatbot_board_bg_button').click(function(e) {

            e.preventDefault();

            var image = wp.media({

                title: 'Custom Agent',

                // mutiple: true if you want to upload multiple files at once

                multiple: false

            })

                .open()

                .on('select', function(e){

                    // This will return the selected image from the Media Uploader, the result is an object

                    var uploaded_image = image.state().get('selection').first();

                    var image_url = uploaded_image.toJSON().url;

                    // Let's assign the url value to the hidden field value and img src.

                    $('#qcld_woo_chatbot_board_bg_image').attr('src',image_url).css({ display: "block" });
                    //$('#qcld_woo_chatbot_board_bg_image').css({ display: "block" });

                    $('#qcld_woo_chatbot_board_bg_path').val(image_url);

                });

        });

        $('.wp_chatbot_custom_agent_button').click(function(e) {

            e.preventDefault();
    
            var image = wp.media({
    
                title: 'Custom Agent',
    
                // mutiple: true if you want to upload multiple files at once
    
                multiple: false
    
            })
    
                .open()
    
                .on('select', function(e){
    
                    // This will return the selected image from the Media Uploader, the result is an object
    
                    var uploaded_image = image.state().get('selection').first();
    
                    var image_url = uploaded_image.toJSON().url;
    
                    // Let's assign the url value to the hidden field value and img src.
    
                    $('#wp_chatbot_custom_agent_src').attr('src',image_url);
    
                    $('#wp_chatbot_custom_agent_path').val(image_url);
                    $('#wp_chatbot_agent_image_custom').val(image_url);
                });
    
        });




});
});