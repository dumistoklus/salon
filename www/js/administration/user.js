orgup.pages.user = {
    run: function() {
        $('#user-show-change-pass-form').click(function(){
            $(this).hide();
            $('#user-change-pass-form').slideDown();
            return false;
        });

        $('#user-hide-change-pass-form').click(function(){
            $('#user-show-change-pass-form').show();
            $('#user-change-pass-form').slideUp();
            return false;
        });
    }
};
