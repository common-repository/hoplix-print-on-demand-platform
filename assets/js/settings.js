var Hoplix_Settings;



(function () {

    'use strict';



    Hoplix_Settings = {

        init_submit: function () {

            

            var form = jQuery('form[name=hoplix_settings]');

            var submit_button = form.find('.woocommerce-save-button');

            var loader = form.find('.loader');

            var pass = form.find('.loader-wrap .pass');

            var fail = form.find('.loader-wrap .fail');



            submit_button.click(function (e) {



                e.preventDefault();

                submit_button.attr('disabled', 'disabled');

                loader.show();



                jQuery.ajax({

                    type: "POST",

                    url: form.attr('action'),

                    data: form.serialize(),

                    success: function (response) {

                        loader.hide();

                        if (response === 'OK') {
                            
                            submit_button.innerHTML('Exported');
                            
                            pass.show(0).delay(3000).hide(0);

                        } else {

                            fail.empty();

                            fail.append('<span class="dashicons dashicons-no"></span>' + response);

                            fail.show(0).delay(3000).hide(0);
                            
                            submit_button.removeAttr('disabled');

                        }

                    }

                });

            });

        },

        enable_submit_btn: function () {

            jQuery('.hoplix-submit input[type=submit]').removeClass('disabled').prop('disabled', false);

        }

    };

})();