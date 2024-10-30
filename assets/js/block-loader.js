var Hoplix_Block_Loader;

(function () {
    'use strict';

    Hoplix_Block_Loader = {
        load: function (ajax_url, block) {
            console.log(block);
            console.log(ajax_url);
            
            block = jQuery('#' + block);
            if (block.length > 0) {

                jQuery.ajax({
                    type: "GET",
                    url: ajax_url,
                    success: function (response) {
                        block.html(response);
                    }
                });
            }
        }
    };
})();