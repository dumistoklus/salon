orgup.pages.pageEditor = {
    run: function() {
        $('#ep-keywords').limit( 255, '#kolkeys' );
        $('#ep-description').limit( 255, '#koldesc' );
        $('#ep-name').limit( 100, '#kolname' );
    }
};