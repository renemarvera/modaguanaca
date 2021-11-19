jQuery(function ($) {
    /******************************
     Jarvis Chat bot
     *********************************/
      //Global
    var userHitNum = 0;
    var confirmaNotName = 0;
    var infiniteChat = 0;
    var chatInitialize = 0;
    var qcld_woow_boot_user_init_name = 0;
    var wooChatBotVar = woo_chatbot_obj;

    var globalwoow={
        initialize:0,
        settings:woo_chatbot_obj,
        wildCard:0,
        wildcards:'',
        bargainStep:'welcome', // bargin welcome message
        bargainId:0, // bargin product id
        bargainVId:0, // bargin product variation id
        bargainPrice:0, // bargin price
        bargainLoop:0, // bargin price
        sendusemailemail: '',
        sendusemailstep: 0,
        sendusemailmsg: '',

    };

    $(document).ready(function(){
        //console.log(wooChatBotVar.map_get_email_address);
        //show it
        $('#woo-chatbot-ball-wrapper').css({
            'display':'block',
        });
        //WooChatBot icon  position.
        $('#woo-chatbot-icon-container').css({
            'right': wooChatBotVar.woo_chatbot_position_x + 'px',
            'bottom': wooChatBotVar.woo_chatbot_position_y + 'px'
        })
        //window resize.
        var widowH=$(window).height();
        var widowW=$(window).width();
        if(widowH<=1200 && widowH>=700 ){
            var ballConH=parseInt(widowH*0.5);
            $('.woo-chatbot-ball-inner').css({ 'height':ballConH+'px'})

            $(window).resize(function(){
                var widowH=$(window).height();
                var ballConH=parseInt(widowH*0.5);
                $('.woo-chatbot-ball-inner').css({ 'height':ballConH+'px'})
            });
        }
        //Woo chat bot show and initial message.
        var botimage = jQuery('#woo-chatbot-ball').find('img').attr('src');
        $(document).on('click', '#woo-chatbot-ball', function (event) {
            $("#woo-chatbot-ball-container").toggle();
            $('.woo-chatbot-ball-inner').slimScroll({height: '50hv',start : 'bottom'});
            localStorage.setItem("wildCard", 0);
			
            if(widowW<=767) {//For mobile
				$('.woo-chatbot-admin').show();
                var headerH = $('.woo-chatbot-admin').outerHeight();
                var footerH = $('.woo-chatbot-editor-container').outerHeight();
                //var AppContentInner = widowH -  footerH - headerH;
                //var AppContentInner = widowH + headerH;
                var AppContentInner = widowH;
				
                //alert(footerH);
                $('.woo-chatbot-ball-inner').css({'height': (AppContentInner-105) + 'px'})
                if ($('#woo-chatbot-mobile-close').length <= 0) {
                    $('.woo-chatbot-admin').append('<div id="woo-chatbot-mobile-close">X</div>');
                }
                $('#woo-chatbot-ball').hide();
                $('#woo-chatbot-icon-container').css({'bottom':'0','left':'0','right':'0'});
                $('.woo-chatbot-ball-container').css({'bottom':'0','left':'0'});
            }

            var qcld_woow_boot_user_init_name = localStorage.getItem("qcld_woow_boot_user_init_name");

           // console.log(qcld_woow_boot_user_init_name);
                     
           //close button
            if($('#woo-chatbot-ball-container').is(':visible')){                    

                $('#woo-chatbot-ball').removeClass('woobot_chatclose_iconanimation');
                $('#woo-chatbot-ball').addClass('woobot_chatopen_iconanimation');
                $('#woo-chatbot-ball').find('img').attr('src', wooChatBotVar.image_path+'woowbot-close-icon.png');
                
            }else{

                $('#woo-chatbot-ball').removeClass('woobot_chatopen_iconanimation');
                $('#woo-chatbot-ball').addClass('woobot_chatclose_iconanimation');
                $('#woo-chatbot-ball').find('img').attr('src', botimage)        
                $('.woo-chatbot-ball').css('background', '#ffffff');
                
            }
            
			if($('#woo-chatbot-ball-container').is(':visible')){
				
			
			
            if(chatInitialize==0 && qcld_woow_boot_user_init_name == null){
                // Working only for bot not user.
                disable_message_editor();

                //Initiliaze message
                var botJoinMsg="<span class='woo-chatbot-agent-joint'><strong>"+wooChatBotVar.agent+" </strong> "+wooChatBotVar.agent_join+"</span>";
                $("#woo-chatbot-messages-container").append('<li class="woo-chatbot-msg"><img class="woo-chatbot-comment-loader" src="'+wooChatBotVar.image_path+'comment.gif" alt="Typing..." /></li>');
               
                setTimeout(function(){
                    $("#woo-chatbot-messages-container li:last").css({'background-color': 'transparent','border':'none'}).html(get_avatar_client_img()+botJoinMsg);
                    $("#woo-chatbot-messages-container").append('<li class="woo-chatbot-msg"><img class="woo-chatbot-comment-loader" src="'+wooChatBotVar.image_path+'comment.gif" alt="Typing..." /></li>');
                    var botInitialeMsg="<span>"+wooChatBotVar.welcome+" <strong>"+wooChatBotVar.host+"!</strong> "+wooChatBotVar.asking_name+"</span>";
                    setTimeout(function(){
                        $("#woo-chatbot-messages-container li:last").html(get_avatar_client_img()+botInitialeMsg);
                        //enable user work
                        enable_message_editor();
                    }, 1500);


                }, 1500);
                   chatInitialize++;
            }else{

                var wooChatBotMsg =wooChatBotVar.i_am +" <strong>"+wooChatBotVar.agent+"</strong>! "+wooChatBotVar.name_greeting+", <strong>"+qcld_woow_boot_user_init_name+"</strong>!";

                //Asking for typing a product!
				if($("#woo-chatbot-messages-container li").length==0){
					setTimeout(function(){
						$("#woo-chatbot-messages-container").append('<li class="woo-chatbot-msg"><img class="woo-chatbot-comment-loader" src="'+wooChatBotVar.image_path+'comment.gif" alt="Typing..." /></li>');
						//Afer 1.5 second show product asking.
						setTimeout(function(){
							$("#woo-chatbot-messages-container li:last").html(get_avatar_client_img()+"<span>"+wooChatBotMsg+"<span>");
							//scroll at the last message.
							$('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');

							$("#woo-chatbot-messages-container").append('<li class="woo-chatbot-msg"><img class="woo-chatbot-comment-loader" src="'+wooChatBotVar.image_path+'comment.gif" alt="Typing..." /></li>');

							setTimeout(function(){
								$("#woo-chatbot-messages-container li:last").html(get_avatar_client_img()+"<span>"+wooChatBotVar.product_asking+"<span>");
								//scroll at the last message.
								$('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');

							}, 1500);

							//enable user work
							enable_message_editor();

							}, 1500);
					}, 1000);
				}
            
       
                
            }
		}

        });
        $(document).on('click', '#woo-chatbot-mobile-close', function (event) {
            $("#woo-chatbot-ball-container").toggle();
            $('#woo-chatbot-icon-container').css({
                'right': '30px',
                'bottom': '30px'});
            $('#woo-chatbot-ball').show();

            //close button
            $('#woo-chatbot-ball').removeClass('woobot_chatopen_iconanimation');
            $('#woo-chatbot-ball').addClass('woobot_chatclose_iconanimation');
            $('#woo-chatbot-ball').find('img').attr('src', botimage)        
            $('.woo-chatbot-ball').css('background', '#ffffff');


        });
        //Hide Woo chat bot box if click on outside of icon.
        // $(document).on('click',function (e) {
        //     var container = $("#woo-chatbot-ball-container");
        //     var rejectContainer = $("#woo-chatbot-ball");
        //     var bargainContainer = $(".woo_minimum_accept_price-bargin");
        //     if(!rejectContainer.is(e.target) && rejectContainer.has(e.target).length === 0){
        //         if (!bargainContainer.is(e.target) && bargainContainer.has(e.target).length === 0) {
        //         if (!container.is(e.target) && container.has(e.target).length === 0) {
        //             container.fadeOut(500);
        //         }
        //         }
        //     }
        // });
        //For send button click
        $(document).on('click',"#woo-chatbot-send-message",function(){

            var wildCardCheck = localStorage.getItem("wildCard");

            var qcld_woow_boot_user_init_name = localStorage.getItem("qcld_woow_boot_user_init_name");

            if( userHitNum == 10 ){
                user_action();
            }else if(wildCardCheck == 9){
                userHitNum = 9 ;
                user_action();
               // console.log('bargain');
                $("#woo-chatbot-messages-container").append('<li class="woo-chatbot-msg"><img class="woo-chatbot-comment-loader" src="'+wooChatBotVar.image_path+'comment.gif" alt="Typing..." /></li>');
            }else if(qcld_woow_boot_user_init_name != null){
                
                userHitNum = 2;
                user_action();

            }else{
                userHitNum++;
                user_action();
               // console.log('bargain not');
            }

            // userHitNum++;
            // user_action();
        })
        //For keyboard enter.
        $("#woo-chatbot-editor").on('keypress',function(event) {
            var wildCardCheck = localStorage.getItem("wildCard");

            var qcld_woow_boot_user_init_name = localStorage.getItem("qcld_woow_boot_user_init_name");
            console.log(userHitNum);
            if (event.which == 13) {
                event.preventDefault();
                if( userHitNum == 10 ){
                    user_action();
                }else if(wildCardCheck == 9){
                    userHitNum = 9 ;
                    user_action();
                     $("#woo-chatbot-messages-container").append('<li class="woo-chatbot-msg"><img class="woo-chatbot-comment-loader" src="'+wooChatBotVar.image_path+'comment.gif" alt="Typing..." /></li>');
                   // console.log('bargain');
                }else if(qcld_woow_boot_user_init_name != null){
                    userHitNum = 2;
                    user_action();
                }else{
                    userHitNum++;
                    user_action();
                }

                
            }
        });

    });


    /******* Bot and user interaction start here***************/
    function htmlTagsScape(userString) {
        var tagsToReplace = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;'
        };
        return userString.replace(/[&<>]/g, function(tag) {
            return tagsToReplace[tag] || tag;
        });
    }
    function user_action(){
        var d = document;
        var userText =$.trim( $("#woo-chatbot-editor").val());
        if(userText != ""){
            userText=htmlTagsScape(userText);
            $("#woo-chatbot-messages-container").append("<li class='woo-chat-user-msg'>"+get_avatar_user_img()+"<span class='woo-chatbot-paragraph'>"+userText+"<span></li>");

            //scroll at the last message.
            $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');
            bot_action(userText);
            $("#woo-chatbot-editor").val("");
        }
    }

    function get_avatar_user_img(){
        return '<div class="woo-chatbot-avatar"><img src="'+wooChatBotVar.agent_image_path+'" alt=""></div>';
    }
    function get_avatar_client_img(){
        return '<div class="woo-chatbot-avatar"><img src="'+wooChatBotVar.agent_image_path+'" alt=""></div>';
    }

    function bot_action(userText) {
        //Disable the input and button when bot will start working..
        disable_message_editor();
        if(userHitNum != 9 ){
            $("#woo-chatbot-messages-container").append('<li class="woo-chatbot-msg"><img class="woo-chatbot-comment-loader" src="'+wooChatBotVar.image_path+'comment.gif" alt="Typing..." /></li>');
        }
        
        infiniteChat=userText
        //Greeting and Name asking part.
        if(userHitNum ==1 && infiniteChat!=1){
            //Checking some common answer excpet name.
            var notName=["sure", "yes","yea", "yeah","no","nope","certainly","never"];
            if(notName.indexOf(userText)>-1 && confirmaNotName==0 ){
                var wooChatBotMsg = "<strong>"+userText+"</strong>  is your name?";
                userHitNum--;
                confirmaNotName++;
                //Asking Name again!
                setTimeout(function(){
                    $("#woo-chatbot-messages-container").append('<li class="woo-chatbot-msg"><img class="woo-chatbot-comment-loader" src="'+wooChatBotVar.image_path+'comment.gif" alt="Typing..." /></li>');
                    //Afer 1.5 second show asking again.
                    setTimeout(function(){
                        // $("#woo-chatbot-messages-container li:last").html("<span>Would you please confirm your name?<span>");
                        $("#woo-chatbot-messages-container li:last").html(get_avatar_client_img()+"<span>Would you please confirm your name?<span>");
                        //scroll at the last message.
                        $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');
                        //enable user work
                        enable_message_editor();

                    }, 1500);
                }, 2000);

            }else{


               localStorage.setItem("qcld_woow_boot_user_init_name",  userText);


                var wooChatBotMsg =wooChatBotVar.i_am +" <strong>"+wooChatBotVar.agent+"</strong>! "+wooChatBotVar.name_greeting+", <strong>"+userText+"</strong>!";

                //Asking for typing a product!
                setTimeout(function(){
                    $("#woo-chatbot-messages-container").append('<li class="woo-chatbot-msg"><img class="woo-chatbot-comment-loader" src="'+wooChatBotVar.image_path+'comment.gif" alt="Typing..." /></li>');
                    //Afer 1.5 second show product asking.
                    setTimeout(function(){
                        $("#woo-chatbot-messages-container li:last").html(get_avatar_client_img()+"<span>"+wooChatBotVar.product_asking+"<span>");
                        //scroll at the last message.
                        $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');
                        //enable user work
                        enable_message_editor();

                        }, 1500);
                }, 2000);
            }
            setTimeout(function(){
                $("#woo-chatbot-messages-container li:last").html(get_avatar_client_img()+"<span>"+wooChatBotMsg+"<span>");
                //scroll at the last message.
                $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');

            }, 1500);
        }
        //For infinite asking answering
        if(userHitNum ==1 && infiniteChat==1){
            console.log( 'hello' );
            setTimeout(function(){
               $("#woo-chatbot-messages-container li:last").html(get_avatar_client_img()+'<span>'+wooChatBotVar.product_infinite+'<span>');
                //scroll at the last message.
                $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');
                //enable user work
                enable_message_editor();

                }, 2500);
        }

        //Product handling steps.
        if(userHitNum ==2){
            //Searching product using given user strings.
            var data = {
                'action':'qcld_woo_chatbot_keyword',
                'keyword':userText,
            };
            $.post(wooChatBotVar.ajax_url, data, function (response) {
                // console.log(response);
                if(response.product_num==0){
                    var wooChatBotMsg = wooChatBotVar.product_fail+" <strong>"+userText+"</strong>!";
                    //suggesting product by category.
                    setTimeout(function(){
                        $("#woo-chatbot-messages-container").append('<li class="woo-chatbot-msg"><img class="woo-chatbot-comment-loader" src="'+wooChatBotVar.image_path+'comment.gif" alt="Typing..." /></li>');
                        //Afer 1.5 second show suggesting.
                        setTimeout(function(){

                            $("#woo-chatbot-messages-container li:last").html(get_avatar_client_img()+"<span>"+ wooChatBotVar.specific_fail +"<span>");
                            
                            var html = '<span class="woobot_product_search qcld-chatbot-button" type="button" >'+ wooChatBotVar.product_search +'</span>';
                            html += '<span class="woobot_catalog qcld-chatbot-button" type="button" >'+ wooChatBotVar.catalog +'</span>';
                            html += '<span class="woobot_send_us_email qcld-chatbot-button" type="button" >'+ wooChatBotVar.send_us_email +'</span>';
                            
                            $("#woo-chatbot-messages-container").append('<li class="woo-chatbot-msg"><img class="woo-chatbot-comment-loader" src="'+wooChatBotVar.image_path+'comment.gif" alt="Typing..." /></li>');

                            $("#woo-chatbot-messages-container li:last").css({'background-color': 'transparent','border':'none'}).html("<div>"+html +"</div>");
                            
                            //scroll at the last message.
                            $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');
                            enable_message_editor()
                        }, 1500);
                    }, 2000);

                   
                }else{
                    var wooChatBotMsg = wooChatBotVar.product_success+" <strong>"+userText+"</strong>!";
                   //Showing product from ajax response
                    setTimeout(function(){
                            $("#woo-chatbot-messages-container").append('<li class="woo-chatbot-msg"><img class="woo-chatbot-comment-loader" src="'+wooChatBotVar.image_path+'comment.gif" alt="Typing..." /></li>');
                            setTimeout(function(){
                            //Afer 1.5 second show categories.
                            $("#woo-chatbot-messages-container li:last").css({'background-color': 'transparent','border':'none','width':'100%'}).html(response.html);
                              //scroll at the last message.
                               $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');
								enable_message_editor()
                            }, 1500);

                        }, 2500);
                    //Setting infinite value as
                    setTimeout(function(){
                        $("#woo-chatbot-send-message").prop("disabled", true);
                        userHitNum=1;
                        bot_action(1);
                    }, 6000);
                }
                setTimeout(function(){
                $("#woo-chatbot-messages-container li:last").html(get_avatar_client_img()+"<span>"+wooChatBotMsg+"<span>");
                    //scroll at the last message.
                    $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');
                    //enable user work
                    //enable_message_editor();

                    }, 1500);
            });
        }
        //category handling steps.
        if(userHitNum ==3){
            var userTexts=userText.split("#");
            var categoryTitle=userTexts[0];
            var categoryId=userTexts[1];
            //Getting product by clicked category.
            var data = {
                'action':'qcld_woo_chatbot_category_products',
                'category':categoryId,
            };
            $.post(wooChatBotVar.ajax_url, data, function (response) {
                if(response.product_num==0){
                    var wooChatBotMsg = wooChatBotVar.product_fail+" <strong>"+categoryTitle+"</strong>!";
                    $("#woo-chatbot-messages-container li:last").html(get_avatar_client_img()+"<span>"+wooChatBotMsg+"</span>");
                    $("#woo-chatbot-messages-container").append('<li class="woo-chatbot-msg"><img class="woo-chatbot-comment-loader" src="'+wooChatBotVar.image_path+'comment.gif" alt="Typing..." /></li>');
                    //scroll at the last message.
                    $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');
                    //suggesting product by category.
                    setTimeout(function(){
                         //Afer 1.5 second show suggesting.
                        setTimeout(function(){
                            $("#woo-chatbot-messages-container li:last").html(get_avatar_client_img()+"<span>"+wooChatBotVar.product_infinite+"<span>");
                            //scroll at the last message.
                            $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');
                            //enable user work
                            enable_message_editor();

                            }, 1500);
                    }, 2000);

                } else{
                    //Now show chat message to choose the product.
                    var wooChatBotMsg = wooChatBotVar.product_success+" <strong>"+categoryTitle+"</strong>!";
                    //Showing Chat boat message with product.
                    $("#woo-chatbot-messages-container li:last").html(get_avatar_client_img()+"<span>"+wooChatBotMsg+"<span>");
                    //scroll at the last message.
                    $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');

                    $("#woo-chatbot-messages-container").append('<li class="woo-chatbot-msg"><img class="woo-chatbot-comment-loader" src="'+wooChatBotVar.image_path+'comment.gif" alt="Typing..." /></li>');
                    setTimeout(function(){
                        $("#woo-chatbot-messages-container li:last").css({'background-color': 'transparent','border':'none','width':'100%'}).html(response.html);
                        //scroll at the last message.
                        $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');


                    }, 1500);
                    //Setting infinite value as
                    setTimeout(function(){
                        userHitNum=1;
                        bot_action(1);
                    }, 5500);

                }


            });

        }

        if( userHitNum == 5 ){
             //Getting the category by ajax to show at the bottom of chat box.
             $("#woo-chatbot-messages-container li:last").html(get_avatar_client_img()+"<span>"+wooChatBotVar.product_suggest+"<span>");
             $("#woo-chatbot-messages-container").append('<li class="woo-chatbot-msg"><img class="woo-chatbot-comment-loader" src="'+wooChatBotVar.image_path+'comment.gif" alt="Typing..." /></li>');
             $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');
            var cat_data = {
            'action':'qcld_woo_chatbot_category',
            };
            $.post(wooChatBotVar.ajax_url, cat_data, function (cat_response) {
            // $("#bot-bottom").html(cat_response);
                setTimeout(function(){
                $("#woo-chatbot-messages-container li:last").css({'background-color': 'transparent','border':'none'}).html("<div>"+cat_response +"</div>");
                    //scroll at the last message.
                    $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');
                    enable_message_editor();
                }, 1500);

            });
        }

        //handling send us email
        if( userHitNum == 10 ){
            
            if( globalwoow.sendusemailstep == 0 ){
                $("#woo-chatbot-messages-container li:last").html(get_avatar_client_img()+"<span>Please provide your email address<span>");
                $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');
                enable_message_editor();
                globalwoow.sendusemailstep = 1;
            }else if( globalwoow.sendusemailstep == 1 && globalwoow.sendusemailemail == '' && userText != '' ){
                globalwoow.sendusemailemail = userText;
                $("#woo-chatbot-messages-container li:last").html(get_avatar_client_img()+"<span>Please write you message<span>");
                $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');
                enable_message_editor();
                globalwoow.sendusemailstep = 2;
            }else if( globalwoow.sendusemailstep == 2 && globalwoow.sendusemailstep != '' && userText != '' ){
                globalwoow.sendusemailmsg = userText;

                var data = {
                    'action':'qcld_woo_chatbot_email',
                    'name': localStorage.getItem("qcld_woow_boot_user_init_name"),
                    'page': window.location.href,
                    'email': globalwoow.sendusemailemail,
                    'message': globalwoow.sendusemailmsg
                };
                $.post(wooChatBotVar.ajax_url, data, function (response) {
                    $("#woo-chatbot-messages-container li:last").html(get_avatar_client_img()+"<span>"+wooChatBotVar.email_successfully+"<span>");
                    $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');
                    enable_message_editor();
                    userHitNum = 2;  
                })

            }

            
        }

        //bargain product
        if(userHitNum == 9 ){


            if(globalwoow.wildCard==9 && globalwoow.bargainStep == 'welcome' && globalwoow.bargainId != ''){

                //alert(userText);
                var data = {
                    'action':'qcld_woo_bargin_product',
                    'qcld_woo_map_product_id':globalwoow.bargainId,
                    'qcld_woo_map_variation_id':globalwoow.bargainVId,
                    'security': wooChatBotVar.map_free_get_ajax_nonce

                };
                //woowKits.ajax(data).done(function (response) {
                $.post(wooChatBotVar.ajax_url, data, function (response) {
                    var restWarning = response.html;
                    var confirmBtn  = wooChatBotVar.your_offer_price;
                   
                    var timerCount = 2500;

                    if($('.woo-chatbot-msg').length > 3){
                        timerCount = 100;
                    }

                    setTimeout(function(){

                        $("#woo-chatbot-messages-container").append('<li class="woo-chatbot-msg"><img class="woo-chatbot-comment-loader" src="'+wooChatBotVar.image_path+'comment.gif" alt="Typing..." /></li>');

                        setTimeout(function(){
                            $("#woo-chatbot-messages-container li.woo-chatbot-msg:last").html(get_avatar_client_img()+restWarning);
                            $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');

                            $("#woo-chatbot-messages-container").append('<li class="woo-chatbot-msg"><img class="woo-chatbot-comment-loader" src="'+wooChatBotVar.image_path+'comment.gif" alt="Typing..." /></li>');

                           

                            if( ($.cookie('qcld_map_product_v_previous_id_'+globalwoow.bargainId) == globalwoow.bargainId) && ($.cookie('qcld_map_product_v_previous_variable_id_'+globalwoow.bargainVId) == globalwoow.bargainVId) ) {

                                setTimeout(function(){

                                    globalwoow.bargainStep  = 'bargain';
                                    
                                    globalwoow.bargainId = $.cookie('qcld_map_product_v_previous_id_'+globalwoow.bargainId) ? $.cookie('qcld_map_product_v_previous_id_'+globalwoow.bargainId) : '';
                                    globalwoow.bargainVId = $.cookie('qcld_map_product_v_previous_variable_id_'+globalwoow.bargainVId) ? $.cookie('qcld_map_product_v_previous_variable_id_'+globalwoow.bargainVId) : '';
                                    globalwoow.bargainPrice = $.cookie('qcld_map_product_v_previous_price_'+globalwoow.bargainVId) ? $.cookie('qcld_map_product_v_previous_price_'+globalwoow.bargainVId) : 0;
                                    
                                    
                                    localStorage.setItem("bargainId",  globalwoow.bargainId);
                                    localStorage.setItem("bargainVId",  globalwoow.bargainVId);
                                    localStorage.setItem("bargainStep",  globalwoow.bargainStep);
                                    localStorage.setItem("bargainPrice",  globalwoow.bargainPrice);

                                    var confirmBtn  = wooChatBotVar.map_acceptable_prev_price.replace("{offer price}", parseFloat(globalwoow.bargainPrice) + globalwoow.settings.currency_symbol);
                                    $("#woo-chatbot-messages-container li.woo-chatbot-msg:last").html(get_avatar_client_img()+'<span>' + confirmBtn  + '<span>');

                                    $("#woo-chatbot-messages-container").append('<li class="woo-chatbot-msg"><img class="woo-chatbot-comment-loader" src="'+wooChatBotVar.image_path+'comment.gif" alt="Typing..." /></li>');
                                    var confirmBtn = '<span class="qcld-modal-bargin-confirm-add-to-cart" confirm-data="yes" >Yes</span> <span> Or </span><span class="qcld-chatbot-bargin-confirm-btn"  confirm-data="no"> No </span>';

                                    $("#woo-chatbot-messages-container li.woo-chatbot-msg:last").html(get_avatar_client_img()+'<span>' + confirmBtn  + '<span>');
                                    $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');
                                }, 1800);

                            }else if( $.cookie('qcld_map_product_previous_id_'+globalwoow.bargainId) == globalwoow.bargainId ) {

                                setTimeout(function(){

                                    globalwoow.bargainStep  = 'bargain';

                                    globalwoow.bargainId = $.cookie('qcld_map_product_previous_id_'+globalwoow.bargainId) ? $.cookie('qcld_map_product_previous_id_'+globalwoow.bargainId) : '';
                                    globalwoow.bargainPrice = $.cookie('qcld_map_product_previous_id_price_'+globalwoow.bargainId) ? $.cookie('qcld_map_product_previous_id_price_'+globalwoow.bargainId) : 0;
                                    globalwoow.bargainVId = '';
                                    
                                    localStorage.setItem("bargainId",  globalwoow.bargainId);
                                    localStorage.setItem("bargainVId",  globalwoow.bargainVId);
                                    localStorage.setItem("bargainStep",  globalwoow.bargainStep);
                                    localStorage.setItem("bargainPrice",  globalwoow.bargainPrice);

                                    var confirmBtn  = wooChatBotVar.map_acceptable_prev_price.replace("{offer price}", parseFloat(globalwoow.bargainPrice) + globalwoow.settings.currency_symbol);
                                    $("#woo-chatbot-messages-container li.woo-chatbot-msg:last").html(get_avatar_client_img()+'<span>' + confirmBtn  + '<span>');

                                    $("#woo-chatbot-messages-container").append('<li class="woo-chatbot-msg"><img class="woo-chatbot-comment-loader" src="'+wooChatBotVar.image_path+'comment.gif" alt="Typing..." /></li>');
                                    var confirmBtn = '<span class="qcld-modal-bargin-confirm-add-to-cart" confirm-data="yes" >Yes</span> <span> Or </span><span class="qcld-chatbot-bargin-confirm-btn"  confirm-data="no"> No </span>';

                                    $("#woo-chatbot-messages-container li.woo-chatbot-msg:last").html(get_avatar_client_img()+'<span>' + confirmBtn  + '<span>');
                                    $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');
                                }, 1800);

                            }else{

                                setTimeout(function(){
                                    $("#woo-chatbot-messages-container li.woo-chatbot-msg:last").html(get_avatar_client_img()+'<span>' + confirmBtn  + '<span>');
                                    $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');
                                }, 1500);

                            }

                        }, 1000);

                    }, timerCount);

     

                    globalwoow.bargainStep = 'bargain';
                    globalwoow.bargainLoop  = 0;
                    globalwoow.bargainPrice = '';
                    globalwoow.bargainId = globalwoow.bargainId;
                    globalwoow.bargainVId = globalwoow.bargainVId;
                    localStorage.setItem("wildCard",  localStorage.getItem("wildCard"));
                    localStorage.setItem("bargainLoop",  globalwoow.bargainLoop);
                    localStorage.setItem("bargainPrice",  globalwoow.bargainPrice);
                    localStorage.setItem("bargainId",  globalwoow.bargainId);
                    localStorage.setItem("bargainVId",  globalwoow.bargainVId);

                    enable_message_editor();

                });


            }else if(globalwoow.wildCard == 9 && globalwoow.bargainStep == 'bargain' && userText !== ""){
                
                    // setTimeout(function(){
                    var string = userText;
                    
                    //var spliting = string.match(/\d+/g);
                    var spliting = string.match(/\d+(?:\.\d+)?/g);
                    
                    if(spliting===null){
                       // woowMsg.single(globalwoow.your_offer_price_again);
                        $("#woo-chatbot-messages-container li:last").html(get_avatar_client_img()+globalwoow.your_offer_price_again);

                    }else{
                        
                    
                        // var msg = string.match(/\d+/g).map(Number);
                        var msg = string.match(/\d+(?:\.\d+)?/g).map(Number);

                        var data = {
                                'action':'qcld_woo_bargin_product_price',
                                'qcld_woo_map_product_id':globalwoow.bargainId,
                                'qcld_woo_map_variation_id':globalwoow.bargainVId, 
                                'price': parseFloat(msg),
                                'security': wooChatBotVar.map_free_get_ajax_nonce
                            };

                        // woowKits.ajax(data).done(function (response) {
                        $.post(wooChatBotVar.ajax_url, data, function (response) {
                            
                            globalwoow.bargainStep  = 'bargain';
                            globalwoow.bargainPrice = response.sale_price;
                            localStorage.setItem("bargainStep",  globalwoow.bargainStep);
                            localStorage.setItem("bargainPrice",  globalwoow.bargainPrice);

                            if(response.confirm == 'success'){
                                // If user provide price below minimum price
                                if( globalwoow.bargainLoop == 1){
                                    var your_low_price_alert  = globalwoow.settings.your_low_price_alert;
                                    var confirmBtn1  = your_low_price_alert.replace("{offer price}", parseFloat(msg) + globalwoow.settings.currency_symbol);
                                    var your_too_low_price_alert  = globalwoow.settings.your_too_low_price_alert;
                                    var restWarning  = your_too_low_price_alert.replace("{minimum amount}", globalwoow.bargainPrice + globalwoow.settings.currency_symbol);

                                    var confirmBtn='<span class="qcld-modal-bargin-confirm-add-to-cart" confirm-data="yes" >Yes</span> <span> Or </span><span class="qcld-chatbot-bargin-confirm-btn"  confirm-data="no"> No </span>';
                                   // woowMsg.triple_nobg(confirmBtn1,restWarning,confirmBtn);

                                    setTimeout(function(){
                                        $("#woo-chatbot-messages-container li.woo-chatbot-msg:last").html(get_avatar_client_img()+'<span>' + confirmBtn1 + '</span>');
                                        $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');

                                        $("#woo-chatbot-messages-container").append('<li class="woo-chatbot-msg"><img class="woo-chatbot-comment-loader" src="'+wooChatBotVar.image_path+'comment.gif" alt="Typing..." /></li>');

                                        setTimeout(function(){
                                            $("#woo-chatbot-messages-container li.woo-chatbot-msg:last").html(get_avatar_client_img()+'<span>' + restWarning + '</span>');
                                            $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');

                                            $("#woo-chatbot-messages-container").append('<li class="woo-chatbot-msg"><img class="woo-chatbot-comment-loader" src="'+wooChatBotVar.image_path+'comment.gif" alt="Typing..." /></li>');

                                            setTimeout(function(){
                                                $("#woo-chatbot-messages-container li.woo-chatbot-msg:last").html(get_avatar_client_img()+'<span>' +  confirmBtn + '</span>');
                                                $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');
                                            }, 1500);

                                        }, 1500);



                                    }, 1500);

                                    globalwoow.bargainLoop  = 0;
                                    localStorage.setItem("bargainLoop",  globalwoow.bargainLoop);

                                    enable_message_editor();

                                }else{
                                    var restWarning= response.html;
                                
                                    setTimeout(function(){
                                        $("#woo-chatbot-messages-container li.woo-chatbot-msg:last").html(get_avatar_client_img()+'<span>' + response.html + '</span>');
                                        $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');
                                    }, 1500);

                                    globalwoow.bargainLoop  = 1;
                                    localStorage.setItem("bargainLoop",  globalwoow.bargainLoop);
                                     enable_message_editor();
                                }


                            }else if(response.confirm == 'agree'){
                                // if user provide resonable price.

                                setTimeout(function(){
                                    $("#woo-chatbot-messages-container li.woo-chatbot-msg:last").html(get_avatar_client_img()+'<span>' + response.html + '</span>');
                                    $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');

                                    $("#woo-chatbot-messages-container").append('<li class="woo-chatbot-msg"><img class="woo-chatbot-comment-loader" src="'+wooChatBotVar.image_path+'comment.gif" alt="Typing..." /></li>');


                                }, 1500);

                                setTimeout(function(){

                                    var data = {
                                            'action':'qcld_woo_bargin_product_confirm',
                                            'qcld_woo_map_product_id':globalwoow.bargainId, 
                                            'price': globalwoow.bargainPrice,
                                            'security': wooChatBotVar.map_free_get_ajax_nonce
                                        };

                                    $.post(wooChatBotVar.ajax_url, data, function (response) {


                                        var confirmBtn='<span class="qcld-modal-bargin-confirm-add-to-cart" confirm-data="yes" > Yes </span> <span> OR </span><span class="qcld-chatbot-bargin-confirm-btn"  confirm-data="no"> No </span>';


                                            setTimeout(function(){
                                                $("#woo-chatbot-messages-container li.woo-chatbot-msg:last").html(get_avatar_client_img()+'<span>' + response.html + '</span>');
                                                $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');

                                                $("#woo-chatbot-messages-container").append('<li class="woo-chatbot-msg"><img class="woo-chatbot-comment-loader" src="'+wooChatBotVar.image_path+'comment.gif" alt="Typing..." /></li>');


                                                setTimeout(function(){
                                                    $("#woo-chatbot-messages-container li.woo-chatbot-msg:last").html(get_avatar_client_img()+'<span>' + confirmBtn + '</span>');
                                                    $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');
                                                }, 1500);

                                            }, 1500);


                                            globalwoow.wildCard = 9;
                                            globalwoow.bargainStep  = 'bargain';
                                            globalwoow.bargainPrice =  globalwoow.bargainPrice;
                                            localStorage.setItem("wildCard",  globalwoow.wildCard);
                                            localStorage.setItem("bargainStep",  globalwoow.bargainStep);
                                            localStorage.setItem("bargainPrice",  globalwoow.bargainPrice);
                                    });

                                },globalwoow.settings.preLoadingTime);

                            }else if(response.confirm == 'default'){

                                var confirmBtn='<span class="qcld-chatbot-bargin-confirm-btn" confirm-data="back"> Back to Start </span>';

                                setTimeout(function(){
                                    $("#woo-chatbot-messages-container li.woo-chatbot-msg:last").html(get_avatar_client_img()+'<span>' + response.html + '</span>');
                                    $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');

                                    $("#woo-chatbot-messages-container").append('<li class="woo-chatbot-msg"><img class="woo-chatbot-comment-loader" src="'+wooChatBotVar.image_path+'comment.gif" alt="Typing..." /></li>');


                                    setTimeout(function(){
                                        $("#woo-chatbot-messages-container li.woo-chatbot-msg:last").html(get_avatar_client_img()+'<span>' + confirmBtn + '</span>');
                                        $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');
                                    }, 1500);


                                }, 1500);

                            }else{

                                setTimeout(function(){
                                    $("#woo-chatbot-messages-container li.woo-chatbot-msg:last").html(get_avatar_client_img()+'<span>' + response.html + '</span>');
                                    $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');
                                }, 1500);

                            }
                            
                        });
                    }
               // },globalwoow.settings.preLoadingTime);

            }else if(globalwoow.wildCard==9 && globalwoow.bargainStep == 'confirm'){

                setTimeout(function(){

                    var data = {'action':'qcld_woo_bargin_product_confirm',
                            'qcld_woo_map_product_id':globalwoow.bargainId, 
                            'price': globalwoow.bargainPrice,
                            'security': wooChatBotVar.map_free_get_ajax_nonce
                        };
                    $.post(wooChatBotVar.ajax_url, data, function (response) {

                        // map_acceptable_price
                        var restWarning = response.html;
                        var map_acceptable_price  = globalwoow.settings.map_acceptable_price;
                        var confirmBtn1  = map_acceptable_price;

                        setTimeout(function(){
                                $("#woo-chatbot-messages-container li.woo-chatbot-msg:last").html(get_avatar_client_img()+'<span>' + confirmBtn1 + '</span>');
                                $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');

                                $("#woo-chatbot-messages-container").append('<li class="woo-chatbot-msg"><img class="woo-chatbot-comment-loader" src="'+wooChatBotVar.image_path+'comment.gif" alt="Typing..." /></li>');

                            setTimeout(function(){
                                    $("#woo-chatbot-messages-container li.woo-chatbot-msg:last").html(get_avatar_client_img()+'<span>' + restWarning + '</span>');
                                    $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');
                            }, 1500);

                        }, 1500);


                        globalwoow.wildCard = 0;
                        globalwoow.bargainStep  = 'welcome';
                        globalwoow.bargainPrice = '';
                        localStorage.setItem("wildCard",  globalwoow.wildCard);
                        localStorage.setItem("bargainStep",  globalwoow.bargainStep);
                        localStorage.setItem("bargainPrice",  globalwoow.bargainPrice);
                        
                        userHitNum = 1;

                    });

                },globalwoow.settings.preLoadingTime);

            }else if(globalwoow.wildCard==9 && globalwoow.bargainStep == 'add_to_cart'){

                setTimeout(function(){

                    if(globalwoow.bargainVId != ''){

                        if ( $.cookie('qcld_map_product_v_previous_variable_id_'+globalwoow.bargainVId) != globalwoow.bargainVId ) {

                            $.cookie('qcld_map_product_v_previous_id_'+globalwoow.bargainId, globalwoow.bargainId);
                            $.cookie('qcld_map_product_v_previous_variable_id_'+globalwoow.bargainVId, globalwoow.bargainVId);
                            $.cookie('qcld_map_product_v_previous_price_'+globalwoow.bargainVId, globalwoow.bargainPrice);

                        }

                        var product_cookie = 'false';
                        if( ($.cookie('qcld_map_product_v_previous_variable_id_'+globalwoow.bargainVId) == globalwoow.bargainVId) ) {
                        
                            globalwoow.bargainId = $.cookie('qcld_map_product_v_previous_id_'+globalwoow.bargainId) ? $.cookie('qcld_map_product_v_previous_id_'+globalwoow.bargainId) : '';
                            globalwoow.bargainVId = $.cookie('qcld_map_product_v_previous_variable_id_'+globalwoow.bargainVId) ? $.cookie('qcld_map_product_v_previous_variable_id_'+globalwoow.bargainVId) : '';
                            globalwoow.bargainPrice = $.cookie('qcld_map_product_v_previous_price_'+globalwoow.bargainVId) ? $.cookie('qcld_map_product_v_previous_price_'+globalwoow.bargainVId) : 0;
                            
                            localStorage.setItem("bargainId",  globalwoow.bargainId);
                            localStorage.setItem("bargainVId",  globalwoow.bargainVId);
                            localStorage.setItem("bargainPrice",  globalwoow.bargainPrice);
                           var product_cookie = 'yes';
                        }

                        var data = {'action':'qcld_woo_bargin_product_variation_add_to_cart',
                                'product_id':globalwoow.bargainId,
                                'variation_id':globalwoow.bargainVId, 
                                'price': globalwoow.bargainPrice,
                                'product_cookie': product_cookie,
                                'security': wooChatBotVar.map_free_get_ajax_nonce
                            };

                    }else{

                        if ( $.cookie('qcld_map_product_previous_id_'+globalwoow.bargainId) != globalwoow.bargainId ) {

                            $.cookie('qcld_map_product_previous_id_'+globalwoow.bargainId, globalwoow.bargainId);
                            $.cookie('qcld_map_product_previous_id_price_'+globalwoow.bargainId, globalwoow.bargainPrice);

                        }

                        var product_cookie = 'false';
                        if( $.cookie('qcld_map_product_previous_id_'+globalwoow.bargainId) == globalwoow.bargainId ) {
                                    
                            globalwoow.bargainId = $.cookie('qcld_map_product_previous_id_'+globalwoow.bargainId) ? $.cookie('qcld_map_product_previous_id_'+globalwoow.bargainId) : '';
                            globalwoow.bargainPrice = $.cookie('qcld_map_product_previous_id_price_'+globalwoow.bargainId) ? $.cookie('qcld_map_product_previous_id_price_'+globalwoow.bargainId) : 0;
                            globalwoow.bargainVId = '';
                            
                            var product_cookie = 'yes';
                            localStorage.setItem("bargainId",  globalwoow.bargainId);
                            localStorage.setItem("bargainVId",  globalwoow.bargainVId);
                            localStorage.setItem("bargainPrice",  globalwoow.bargainPrice);
                        }

                       var data = {
                        'action':'qcld_woo_bargin_product_add_to_cart',
                        'product_id':globalwoow.bargainId, 
                        'price': globalwoow.bargainPrice,
                        'product_cookie': product_cookie,
                        'security': wooChatBotVar.map_free_get_ajax_nonce
                        };
                    }


                   // woowKits.ajax(data).done(function (response) {
                    $.post(wooChatBotVar.ajax_url, data, function (response) {

                        // map_acceptable_price
                        var restWarning = response.html;

                        var confirmBtn='<div class="woo-chatbot-product-bargain-btn"><a href="'+globalwoow.settings.map_get_checkout_url +'" class="qcld-modal-bargin-confirm-btn-checkout"> '+globalwoow.settings.map_checkout_now_button_text+' </a></div>';


                        setTimeout(function(){
                                $("#woo-chatbot-messages-container li.woo-chatbot-msg:last").html(get_avatar_client_img()+'<span>' + restWarning + '</span>');
                                $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');

                                $("#woo-chatbot-messages-container").append('<li class="woo-chatbot-msg"><img class="woo-chatbot-comment-loader" src="'+wooChatBotVar.image_path+'comment.gif" alt="Typing..." /></li>');

                            setTimeout(function(){
                                    $("#woo-chatbot-messages-container li.woo-chatbot-msg:last").html(get_avatar_client_img()+'<span>' + confirmBtn + '</span>');
                                    $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');
                            }, 1500);

                        }, 1500);


                        globalwoow.wildCard = 0;
                        globalwoow.bargainStep  = 'welcome';
                        globalwoow.bargainVId = '';
                        globalwoow.bargainPrice = '';
                        localStorage.setItem("wildCard",  globalwoow.wildCard);
                        localStorage.setItem("bargainStep",  globalwoow.bargainStep);
                        localStorage.setItem("bargainVId",  globalwoow.bargainVId);
                        localStorage.setItem("bargainPrice",  globalwoow.bargainPrice);
                        
                        userHitNum = 1;
                    });

                },globalwoow.settings.preLoadingTime);

            }else if(globalwoow.wildCard==9 && globalwoow.bargainStep == 'disagree' && globalwoow.bargainLoop == 0){

                    //  map_talk_to_boss msg
                    var map_talk_to_boss  = globalwoow.settings.map_talk_to_boss;
                    var confirmBtn  = map_talk_to_boss;
                    //woowMsg.single(confirmBtn);
                    setTimeout(function(){
                            $("#woo-chatbot-messages-container li.woo-chatbot-msg:last").html(get_avatar_client_img()+'<span>' + confirmBtn + '</span>');
                            $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');
                    }, 1500);

                    globalwoow.bargainLoop = 1;
                    localStorage.setItem("bargainLoop",  globalwoow.bargainLoop);
    

            }else if(globalwoow.wildCard==9 && globalwoow.bargainStep == 'disagree' && globalwoow.bargainLoop == 1){

                var string = userText;
                var spliting = string.match(/\d+/g);
                    
                if(spliting===null){
                   // woowMsg.single(globalwoow.settings.your_offer_price_again);
                    setTimeout(function(){
                            $("#woo-chatbot-messages-container li.woo-chatbot-msg:last").html(get_avatar_client_img()+'<span>' + globalwoow.settings.your_offer_price_again + '</span>');
                            $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');
                    }, 1500);

                }else{
                    // map_get_email_address
                    var map_get_email_address  = globalwoow.settings.map_get_email_address;
                    var confirmBtn  = map_get_email_address;
                   // woowMsg.single(confirmBtn);  
                    setTimeout(function(){
                            $("#woo-chatbot-messages-container li.woo-chatbot-msg:last").html(get_avatar_client_img()+'<span>' + confirmBtn + '</span>');
                            $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');
                    }, 1500);

                   // var string = msg;
                   // globalwoow.bargainPrice = userText.match(/\d+/g).map(Number);
                    globalwoow.bargainPrice = userText.match(/\d+(?:\.\d+)?/g).map(Number);
                    //globalwoow.bargainPrice = msg;
                    //localStorage.setItem("wildCard",  globalwoow.bargainPrice);
                    localStorage.setItem("finalprice",  globalwoow.bargainPrice);

                    
                    globalwoow.bargainLoop = 2;
                    localStorage.setItem("bargainLoop",  globalwoow.bargainLoop);
                }
            }else if(globalwoow.wildCard==9 && globalwoow.bargainStep == 'disagree' && globalwoow.bargainLoop == 2){

                // map_get_email_address
                var map_thanks_test  = globalwoow.settings.map_thanks_test;
                var confirmBtn  = map_thanks_test;

                setTimeout(function(){
                    
                    //woowMsg.single(confirmBtn); 
                    setTimeout(function(){
                            $("#woo-chatbot-messages-container li.woo-chatbot-msg:last").html(get_avatar_client_img()+'<span>' + confirmBtn + '</span>');
                            $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');
                    }, 1500);

                    var data = {'action':'qcld_woo_bargin_send_query',
                                'qcld_woo_map_product_id':globalwoow.bargainId, 
                                'price':  localStorage.getItem("finalprice"), 
                                'email': msg,
                                'security': wooChatBotVar.map_free_get_ajax_nonce
                            };
                    
                    $.post(wooChatBotVar.ajax_url, data, function (response) {
                        //console.log(response);
                       // woowMsg.single(confirmBtn);  

                    });

                },globalwoow.settings.preLoadingTime);

                globalwoow.bargainLoop = 0;
                localStorage.setItem("bargainLoop",  globalwoow.bargainLoop);
                globalwoow.wildCard = 0;
                globalwoow.bargainStep  = 'welcome';
                globalwoow.bargainPrice = '';
                localStorage.setItem("wildCard",  globalwoow.wildCard);
                localStorage.setItem("bargainStep",  globalwoow.bargainStep);
                localStorage.setItem("bargainPrice",  globalwoow.bargainPrice);

                userHitNum =1;

            }

            enable_message_editor();


        }



    }




    //When user click on the category then product will be show.
    $(document).on('click','.qcld-chatbot-product-category',function(){
        userHitNum = 3;
        var nameCatID=$(this).text()+'#'+$(this).attr('data-category-id');
        //Now hide the category and show the category for user.
        $("#woo-chatbot-messages-container .woo-chatbot-msg:last").fadeOut(1500);
        $("#woo-chatbot-messages-container").append("<li class='woo-chat-user-msg'><span>"+$(this).text()+"<span></li>");
        //scroll at the last message.
        $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');

        bot_action(nameCatID);
    });
    
    $(document).on('click','.woobot_product_search',function(){
        userHitNum = 2;
        $("#woo-chatbot-messages-container").append("<li class='woo-chat-user-msg'>"+get_avatar_user_img()+"<span class='woo-chatbot-paragraph'>"+$(this).text()+"<span></li>");
        $("#woo-chatbot-messages-container").append('<li class="woo-chatbot-msg"><img class="woo-chatbot-comment-loader" src="'+wooChatBotVar.image_path+'comment.gif" alt="Typing..." /></li>');
        $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');
        setTimeout(function(){
            $("#woo-chatbot-messages-container li:last").html(get_avatar_client_img()+"<span>"+wooChatBotVar.product_asking+"<span>");
            //scroll at the last message.
            $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');

        }, 1500);
    });

    $(document).on('click','.woobot_catalog',function(){
        userHitNum = 5;
        $("#woo-chatbot-messages-container").append("<li class='woo-chat-user-msg'>"+get_avatar_user_img()+"<span class='woo-chatbot-paragraph'>"+$(this).text()+"<span></li>");
        bot_action();
    });

    $(document).on('click','.woobot_send_us_email',function(){
        userHitNum = 10;
        globalwoow.sendusemailstep = 0;
        $("#woo-chatbot-messages-container").append("<li class='woo-chat-user-msg'>"+get_avatar_user_img()+"<span class='woo-chatbot-paragraph'>"+$(this).text()+"<span></li>");
        bot_action();
    });


    function disable_message_editor(){
        $("#woo-chatbot-editor").attr('placeholder',wooChatBotVar.agent+' '+wooChatBotVar.is_typing);
        $("#woo-chatbot-editor").attr('disabled',true);
        $("#woo-chatbot-send-message").attr('disabled',true);
    }
    function enable_message_editor(){
        $("#woo-chatbot-editor").attr('disabled',false).focus();
        $("#woo-chatbot-editor").attr('placeholder',wooChatBotVar.send_a_msg);
        $("#woo-chatbot-send-message").attr('disabled',false);
    }

    // bargain ...


    //bargain initiate function
    $(document).on('click', '.woo_minimum_accept_price-bargin', function(e){
       // alert('data id w');
        var product_id = $(this).attr('product_id');
        var variation_id = '';

        var variable_check = $('.woo_minimum_accept_price-bargin').parent().parent().find('.variation_id');

        if($( variable_check ).hasClass( "variation_id" )){

            var variation_id = $('.variation_id').val();

            if( variation_id == '0' || variation_id == '' ) {
                alert('Please select some product options before adding this product to your cart.');
                return false;
            }

        }
        
        if($('#woo-chatbot-ball-container').css('display') == 'none'){
            $('#woo-chatbot-ball').trigger('click');
        }

        userHitNum = 9;

        globalwoow.wildCard = 9;
        globalwoow.bargainStep = 'welcome';
        globalwoow.bargainId = product_id;
        globalwoow.bargainVId = variation_id;
        globalwoow.bargainPrice = '';
        localStorage.setItem("wildCard",  globalwoow.wildCard);
        localStorage.setItem("bargainStep",  globalwoow.bargainStep);
        localStorage.setItem("bargainId",  globalwoow.bargainId);
        localStorage.setItem("bargainPrice",  globalwoow.bargainPrice);
        localStorage.setItem("bargainVId",  globalwoow.bargainVId);

        bot_action();

        $('.woo-chatbot-ball-inner').animate({ scrollTop: $('#woo-chatbot-messages-container').prop("scrollHeight")}, 'slow');
        
    });

        // bargain confirm ...
    $(document).on('click','.qcld-chatbot-bargin-confirm-btn',function (e) {
        e.preventDefault();
        var shopperChoice=$(this).text();
        //woowMsg.shopper_choice(shopperChoice);
        var actionType=$(this).attr('confirm-data');

        $("#woo-chatbot-messages-container li.woo-chatbot-msg:last").html('');

        $("#woo-chatbot-messages-container").append("<li class='woo-chat-user-msg'>"+get_avatar_user_img()+"<span>"+shopperChoice+"<span></li>");

        $("#woo-chatbot-messages-container").append('<li class="woo-chatbot-msg"><img class="woo-chatbot-comment-loader" src="'+wooChatBotVar.image_path+'comment.gif" alt="Typing..." /></li>');
        

        if(actionType=='yes'){

            globalwoow.bargainStep = 'confirm';
            localStorage.setItem("bargainStep",  globalwoow.bargainStep);
            bot_action();
        } else if(actionType=='no'){
            globalwoow.bargainStep = 'disagree';
            localStorage.setItem("bargainStep",  globalwoow.bargainStep);
            globalwoow.bargainLoop = 0;
            localStorage.setItem("bargainLoop",  globalwoow.bargainLoop);
            bot_action();
        }else{

            localStorage.setItem("wildCard",  0);
            localStorage.setItem("bargainStep", 'welcome');
            localStorage.setItem("bargainId",  '');
            userHitNum=1;
            bot_action(1);
        }
    });
    $(document).on('click','.qcld-modal-bargin-confirm-add-to-cart',function (e) {
        e.preventDefault();
        var shopperChoice=$(this).text();
        //woowMsg.shopper_choice(shopperChoice);
        $("#woo-chatbot-messages-container li.woo-chatbot-msg:last").html('');
        $("#woo-chatbot-messages-container").append("<li class='woo-chat-user-msg'>"+get_avatar_user_img()+"<span>"+shopperChoice+"<span></li>");

        $("#woo-chatbot-messages-container").append('<li class="woo-chatbot-msg"><img class="woo-chatbot-comment-loader" src="'+wooChatBotVar.image_path+'comment.gif" alt="Typing..." /></li>');
      

        globalwoow.bargainId = localStorage.getItem('bargainId');
        globalwoow.bargainVId = localStorage.getItem('bargainVId');
        globalwoow.bargainPrice = localStorage.getItem('bargainPrice');

        globalwoow.bargainStep = 'add_to_cart';
        localStorage.setItem("bargainStep",  globalwoow.bargainStep);
        bot_action();
    });



    


});