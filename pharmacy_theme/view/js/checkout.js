window.addEventListener("load", function(event) {

    let language = $('html').attr('lang');

    //
    //
    // MULTISTEP FORM CONTROL BUTTONS
    //
    //

    $('#content .next').each(function(index, next_button) {
        $(next_button).click(function(e) {

            // current fieldset position
            fieldset_current = $(this).parent().parent();
    
            // next fieldset position
            fieldset_next = $(this).parent().parent().next();
            
            // shipping address, guests
            if ($(fieldset_current).data('fieldset') == 'register') {

                chain.attach(function() {
                    return $.ajax({
                        url: 'index.php?route=pharmacy/checkout/register.save&language=' + language,
                        type: 'post',
                        dataType: 'json',
                        data: $('#form-register').serialize(),
                        contentType: 'application/x-www-form-urlencoded',
                        beforeSend: function() {
                            $(next_button).button('loading');
                        },
                        complete: function() {
                            $(next_button).button('reset');
                        },
                        success: function(json) {

                            // console.log(json);
          
                            $('#form-register').find('.is-invalid').removeClass('is-invalid');
                            $('#form-register').find('.invalid-feedback').removeClass('d-block');
          
                            if (json['redirect']) {
                                location = json['redirect'];
                            }
          
                            if (json['error']) {

                                if (json['error']['warning']) {
                                    show_message(json['error']['warning'], 'danger');
                                }
          
                                for (key in json['error']) {
                                    $('#input-' + key.replaceAll('_', '-')).addClass('is-invalid').find('.form-control, .form-select, .form-check-input, .form-check-label').addClass('is-invalid');
                                    $('#error-' + key.replaceAll('_', '-')).html(json['error'][key]).addClass('d-block');
                                }
                            }
          
                            if (json['success']) {

                                // user info
                                show_message(json['success'], 'success');

                                // if user account created
                                if ($('#input-register').prop('checked')) {
                                    $('input[name=\'account\']').prop('disabled', true);
                                    $('#input-customer-group').prop('disabled', true);
                                    $('#input-password').prop('disabled', true);
                                    $('#input-captcha').prop('disabled', true);
                                    $('#input-email').prop('readonly', true);
                                    $('#input-register-agree').prop('disabled', true);
                                }
          
                                // show next field
                                $('#checkout-shipping-method-econt-iframe').html('');
                                $('#checkout-shipping-method').load('index.php?route=pharmacy/checkout/shipping_method.confirm&language=' + language);
                                show_next_fieldset(fieldset_current, fieldset_next);

                            }

                            // remove alert (user info)
                            window.setTimeout(function() {
                                $('.alert-dismissible').fadeTo(1000, 0, function() {
                                    $(this).remove();
                                });
                            }, 3500);

                        },
                        error: function(xhr, ajaxOptions, thrownError) {
                            console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                        }
                    });
                });

            }

            // shipping address, registered users
            else if ($(fieldset_current).data('fieldset') == 'shipping-address') {

                if ($('#input-shipping-address').val() || $('#input-shipping-address-voucher').val()) {
                    $('#checkout-shipping-method-econt-iframe').html('');
                    $('#checkout-shipping-method').load('index.php?route=pharmacy/checkout/shipping_method.confirm&language=' + language);
                    show_next_fieldset(fieldset_current, fieldset_next);
                } else {
                    $('#input-shipping-address').trigger('change');
                }
                
            }
            
            // shipping method
            else if ($(fieldset_current).data('fieldset') == 'shipping-method') {

                // check econt form
                let iframe = $('#checkout-shipping-method-econt-iframe').html();

                if (iframe) {
                    alert($('#error-shipping-method-econt').text());
                }
                else if ($('input[name=\'shipping_method\']:checked').length > 0) {
                    $('#checkout-payment-method').load('index.php?route=pharmacy/checkout/payment_method.confirm&language=' + language);
                    show_next_fieldset(fieldset_current, fieldset_next);
                } else {
                    $('.shipping-method').addClass('is-invalid');
                    $('#error-shipping-method').removeClass('d-none').addClass('d-block');
                }
                
            }
            
            // payment method
            else if ($(fieldset_current).data('fieldset') == 'payment-method') {

                if ($('input[name=\'payment_method\']:checked').length > 0) {
                    $('#checkout-confirm').load('index.php?route=pharmacy/checkout/confirm.confirm&language=' + language);
                    show_next_fieldset(fieldset_current, fieldset_next);
                } else {
                    $('.payment-method').addClass('is-invalid');
                    $('#error-payment-method').removeClass('d-none').addClass('d-block');
                }
                
            }

        });
    });

    function show_next_fieldset(fieldset_current, fieldset_next) {
        // update progressbar active icon
        $('#' + $(fieldset_next).data('progressbar')).addClass('active');

        // display next fieldset
        $(fieldset_current).css({ 'display': 'none'});
        $(fieldset_next).css({ 'display': 'block'});

        // animate next fieldset
        $(fieldset_next).css({ opacity: 0 }).animate({ opacity: 1 }, 600);

        // scroll to top of checkout form
        let top_correction = ($(document).width() > 992 ? 250 : 250);
        $(document).scrollTop($("#checkout-progressbar").offset().top - top_correction);

    }

    $('#content .previous').each(function(index, previous_button) {
        $(previous_button).click(function(e) {

            // previous fieldset position
            fieldset_previous = $(this).parent().parent().prev();

            // current fieldset position
            fieldset_current = $(this).parent().parent();
                    
            // update progressbar active icon
            $('#' + $(fieldset_current).data('progressbar')).removeClass('active');

            // display previous fieldset
            $(fieldset_current).css({ 'display': 'none'});
            $(fieldset_previous).css({ 'display': 'block'});

            // animate next fieldset
            $(fieldset_previous).css({ opacity: 0 }).animate({ opacity: 1 }, 600);

        });
    });


    //
    //
    // SHIPPING ADDRESS
    //
    //




    //
    //
    // SHIPPING METHODS
    //
    //


    $(document).on('submit', '#form-shipping-method', function(e) {
        e.preventDefault();

        chain.attach(function () {
            return $.ajax({
                url: 'index.php?route=pharmacy/checkout/shipping_method.save&language=' + language,
                type: 'post',
                data: $('#form-shipping-method').serialize(),
                dataType: 'json',
                contentType: 'application/x-www-form-urlencoded',
                beforeSend: function() {
                    $('#button-shipping-method').button('loading');
                },
                complete: function() {
                    $('#button-shipping-method').button('reset');
                },
                success: function(json) {

                    // console.log('shipping methods');
                    // console.log(json);

                    if (json['redirect']) {
                        location = json['redirect'];
                    }

                    if (json['error']) {
                        show_message(json['error'], 'danger');
                    }

                    if (json['success']) {
                        show_message(json['success'], 'success');

                        // hide form error message
                        $('.shipping-method').removeClass('is-invalid');
                        $('#error-shipping-method').removeClass('d-block');

                        // reload checkout parts
                        $('.payment-method').prop('checked', false);
                        // $('#checkout-payment-method').load('index.php?route=pharmacy/checkout/payment_method.confirm&language=' + language);
                        // $('#checkout-confirm').load('index.php?route=pharmacy/checkout/confirm.confirm&language=' + language);
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        });

    });


    //
    //
    // PAYMENT METHODS
    //
    //


    $(document).on('change', '.payment-method', function(e) {
        $('#form-payment-method').submit();
    });

    $(document).on('submit', '#form-payment-method', function(e) {
        e.preventDefault();

        chain.attach(function() {
            return $.ajax({
                url: 'index.php?route=pharmacy/checkout/payment_method.save&language=' + language,
                type: 'post',
                data: $('#form-payment-method').serialize(),
                dataType: 'json',
                contentType: 'application/x-www-form-urlencoded',
                beforeSend: function() {
                    $('#button-payment-method').button('loading');
                },
                complete: function() {
                    $('#button-payment-method').button('reset');
                },
                success: function(json) {

                    // console.log('payment methods');
                    // console.log(json);

                    if (json['redirect']) {
                        location = json['redirect'];
                    }

                    if (json['error']) {
                        show_message(json['error'], 'danger');
                    }

                    if (json['success']) {
                        show_message(json['success'], 'success');

                        // hide form error message
                        $('.payment-method').removeClass('is-invalid');
                        $('#error-payment-method').removeClass('d-block').addClass('d-none');

                        // (re)load checkout parts
                        // $('#checkout-confirm').load('index.php?route=pharmacy/checkout/confirm.confirm&language=' + language);
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        });
    });




    //
    //
    // ADD COMMENT TO ORDER
    //
    //

    let timer = '';
    $(document).on('keydown', '#input-comment', function() {

        // Request
        clearTimeout(timer);

        timer = setTimeout(function(object) {
            chain.attach(function() {
                return $.ajax({
                    url: 'index.php?route=pharmacy/checkout/payment_method.comment&language=' + language,
                    type: 'post',
                    data: $('#input-comment').serialize(),
                    dataType: 'json',
                    contentType: 'application/x-www-form-urlencoded',
                    success: function(json) {

                        // console.log(json);

                        $('.alert-dismissible').remove();

                        if (json['redirect']) {
                            location = json['redirect'];
                        }

                        if (json['error']) {
                            show_message(json['error'], 'danger');
                        }

                        if (json['success']) {
                            show_message(json['success'], 'success');
                        }

                        window.setTimeout(function() {
                            $('.alert-dismissible').fadeTo(1000, 0, function() {
                                $(this).remove();
                            });
                        }, 3000);
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                    }
                });
            });
        }, 1000, this);
    });


    //
    //
    // SUBMIT ORDER
    //
    //


    // agree to terms
    $(document).on('change', '#input-checkout-agree', function() {
        if ($('#input-checkout-agree:checked').length > 0) {
            $('#button-confirm').removeClass('disabled');
        } else {
            $('#button-confirm').addClass('disabled');
        }

        chain.attach(function() {
            return $.ajax({
                url: 'index.php?route=pharmacy/checkout/payment_method.agree&language=' + language,
                type: 'post',
                data: $('#input-checkout-agree').serialize(),
                dataType: 'json',
                contentType: 'application/x-www-form-urlencoded',
                success: function(json) {
                    $('#checkout-confirm').load('index.php?route=pharmacy/checkout/confirm.confirm&language=' + language);
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        });
    });
    $('#input-checkout-agree').trigger('change');
    
    // button submit order
    $(document).on('click', '#button-confirm', function() {

        let element = this;
        let payment = $(element).data('payment');
        let url = 'index.php?route=extension/pharmacy_checkout/payment/';

        switch (payment) {

            // payment type: bank transfer
            case 'pharmacy_bank_transfer':
                url += 'pharmacy_bank_transfer.confirm&language=' + language;
                break;

            // payment type: cod
            case 'pharmacy_cod':
                url += 'pharmacy_cod.confirm&language=' + language;
                break;
        
            // payment type: econt
            case 'pharmacy_econt':
                url += 'pharmacy_econt.confirm&language=' + language;
                break;

            default:
                location = 'index.php?route=pharmacy/checkout/checkout&language=' + language;
                break;
        }

        // confirm order
        $.ajax({
            url: url,
            dataType: 'json',
            beforeSend: function () {
                $(element).button('loading');
            },
            complete: function () {
                $(element).button('reset');
            },
            success: function (json) {
                if (json['error']) {
                    show_message(json['error'], 'danger');
                }
                if (json['redirect']) {
                    location = json['redirect'];
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });





    }); // submit end


});