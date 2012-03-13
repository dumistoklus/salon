orgup.pages.mainpage = {

    run: function() {

        function mycarousel_initCallback(carousel)
        {

            // Pause autoscrolling if the user moves with the cursor over the clip.
            carousel.clip.hover(function() {
                carousel.stopAuto();
            }, function() {
                carousel.startAuto();
            });
        }

        $('#ramka').jcarousel({
            auto: 4,
            wrap: 'last',
            initCallback: mycarousel_initCallback,
            itemFallbackDimension: 300,
            animation: 'slow'
        });
    }
};