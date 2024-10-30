var Hoplix_Orders;



(function () {

    'use strict';



    Hoplix_Orders = {

        init_submit: function () {

            var form = jQuery('form[name=hoplix_order_export]');

            var submit_button = jQuery('.hoplix-woocommerce-export-order');
            
            


            submit_button.click(function (e) {

                e.preventDefault();
                
                var orderID =   submit_button.attr("id");
                
                var loader  =   jQuery('.loader-'+orderID);

                var pass    =   jQuery('.loader-wrap-'+orderID+' .pass');

                var fail    =   jQuery('.loader-wrap-'+orderID+' .fail');
                
                submit_button.attr('disabled', 'disabled');

                loader.show();



                jQuery.ajax({

                    type: "POST",

                    url: form.attr('action'),

                    data: {id: orderID},

                    success: function (response) {

                        submit_button.removeAttr('disabled');

                        loader.hide();

                        if (response === 'OK0') {
                            
                            document.getElementById(orderID).innerHTML = 'Exported';
                            
                            document.getElementById(orderID).disabled = true;
                            
                            pass.show(0).delay(3000).hide(0);

                        } else {

                            fail.empty();

                            fail.append('<span class="dashicons dashicons-no"></span>' + response);

                            fail.show(0).delay(10000).hide(0);
                            
                        }

                    }

                });

            });

        },

        enable_submit_btn: function () {

            //jQuery('.hoplix-woocommerce-export-order').removeClass('disabled').prop('disabled', false);

        }

    };

})();