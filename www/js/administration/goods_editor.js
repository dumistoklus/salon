orgup.pages.goods_editor = {

    _lastFileId: 1,

    run: function() {

        $('#goods-add-image').click(function(){

            var html = '<div class="goods-file-input"><input name="image['+( ++orgup.pages.goods_editor._lastFileId )+']" type="file"></div>';
            $('#goods-files').append( html );
        });
    }
};
