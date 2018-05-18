define(['jquery'], function($) {
    return {
        init: function() {
            // if the dropbtn is clicked show the drop down content
            $(".dropbtn").click(function(){
                $('.dropdown-content').toggle();
            });

            // if clicked a drop down option
            $(".cm_dropdown_option").click(function( event ){
                var click_this = $('#toggle-'+$(this).attr('id'));

                // if the target element has a the_toggle class prevent the original
                // <a href> of the target from happening but let JS do the job
                if ($(click_this).find('.the_toggle').first().length > 0){
                    event.preventDefault();
                    var new_position = $(click_this).offset();
                    window.scrollTo(new_position.left,new_position.top-68);
                }

                // if the target element is closed click on it to open it
                if ($(click_this).find('.the_toggle').first().hasClass('toggle_closed')){
                    $(click_this).click();
                }
            });

            // when clicked elsewhere just hide the drop down options again
            $(document).click(function(){
                if (!event.target.matches('.dropbtn')) {
                    $('.dropdown-content').hide();
                }
            });

            // if the page has toggle elements show the close_all button
            if($(document).find('.the_toggle').length > 0){
                $('.close_all').show();
            }

            // when the close_all button is clicked close all open elements on the page
            $(".close_all").click(function(){
                $('.the_toggle').each(function(){
                    if($(this).hasClass('toggle_open')){
                        $(this).click();
                    }
                });
            });
        }
    };
});
