define(['jquery'], function($) {

    return {
        init: function() {

            $(".dropbtn").click(function(){
                $('.dropdown-content').toggle();
            });

            $(document).click(function(){
                if (!event.target.matches('.dropbtn')) {
                    $('.dropdown-content').hide();
                }
            });

        }
    };
});
