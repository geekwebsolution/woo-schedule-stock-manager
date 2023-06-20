jQuery(document).ready(function () {
    /* show date field on custom date select */
    jQuery('body').on('change', '.wssmgk_variation_schedule', function () {

        var variation_schedule_type = jQuery(this).children("option:selected").val();

        if (variation_schedule_type == 'wssmgk_custom_date') {
            jQuery(this).parents('.wssmgk_variation_opt').find('.default').hide();
            jQuery(this).parents('.wssmgk_variation_opt').find('.custom_date_and_time').show();
            jQuery(this).parents('.wssmgk_variation_opt').find('.wssmgk_select_start_time').show();
        } else {
            if (variation_schedule_type != '') {
                jQuery(this).parents('.wssmgk_variation_opt').find('.wssmgk_select_start_time').show();
                jQuery(this).parents('.wssmgk_variation_opt').find('.wssmgk_select_time').hide();
                jQuery(this).parents('.wssmgk_variation_opt').find('.default').show();
            }
            else {
                jQuery(this).parents('.wssmgk_variation_opt').find('.wssmgk_select_start_time').hide();
                jQuery(this).parents('.wssmgk_variation_opt').find('.wssmgk_select_time').hide();
                jQuery(this).parents('.wssmgk_variation_opt').find('.default').show();
            }
            jQuery(this).parents('.wssmgk_variation_opt').find('.custom_date_and_time').hide();
        }
    });

    jQuery('body').on('click', '.wssmgkp_disable_field .wc-radios,.wssmgkp_disable_variable_field .wc-radios', function () {
        jQuery(this).parents(".wssmgk_advance_option").find("p.wssmgk_schedule_quantity_type").show();
        console.log("click fdredf");
    });

    /* schedule stock manage event */
    jQuery('body').on('click', '.wssmgk_variation_opt input[type="checkbox"]', function () {
        if (jQuery(this).prop("checked") == true) {
            jQuery(this).parent().next().show();
        }
        else if (jQuery(this).prop("checked") == false) {
            jQuery(this).parent().next().hide();
        }
    });

    /* show pro note on radio button click */
    jQuery('body').on('click', '.wssmgk_variation_opt input[type="radio"]', function () {
        console.log("click fired");
        var sch_qty_type = jQuery('input[name="wssmgkp_disable"]:checked').val();
        var sch_qty_type_var = jQuery('input[name="wssmgkp_disable_variable"]:checked').val();
        if (sch_qty_type == 'update_stock_quantity' || sch_qty_type_var == 'update_stock_quantity') {
            console.log("cond true");
            jQuery(this).parents('.wssmgk_advance_option').find('.wssmgk_schedule_quantity_type').show();
        } else {
            console.log("cond false");
            jQuery(this).parents('.wssmgk_advance_option').find('.wssmgk_schedule_quantity_type').hide();
        }
    });
});