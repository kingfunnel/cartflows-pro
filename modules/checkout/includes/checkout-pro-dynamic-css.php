<?php
/**
 * Dynamic checkout css
 *
 * @package cartflows
 */

// phpcs:ignore

$output .= "

	#wcf-quick-view-content{
		font-family: {$base_font_family};
	}
	#wcf-quick-view-content .summary-content .product_title{
		color: {$section_heading_color};
		font-family: {$heading_font_family};
	    font-weight: {$heading_font_weight};
	}
	#wcf-quick-view-content .summary-content .variations select,
	.wcf-qty-options .wcf-qty-selection{
		color: {$field_color};
		background: {$field_bg_color};
		border-color: {$field_border_color};
		padding-top: {$field_tb_padding}px;
		padding-bottom: {$field_tb_padding}px;
		padding-left: {$field_lr_padding}px;
		padding-right: {$field_lr_padding}px;
		min-height: {$field_input_size};
		font-family: {$input_font_family};
	    font-weight: {$input_font_weight};
	}
	#wcf-quick-view-content .summary-content .single_variation_wrap .woocommerce-variation-add-to-cart button{
		color: {$submit_color};
		background: {$submit_bg_color};
		padding-top: {$submit_tb_padding}px;
		padding-bottom: {$submit_tb_padding}px;
		padding-left: {$submit_lr_padding}px;
		padding-right: {$submit_lr_padding}px;
		border-color: {$submit_border_color};
		min-height: {$submit_button_height};
		font-family: {$button_font_family};
	    font-weight: {$button_font_weight};
	}
	#wcf-quick-view-content .summary-content a{
		color: {$primary_color};
	}
	#wcf-quick-view-content .summary-content .woocommerce-product-rating .star-rating, 
	#wcf-quick-view-content .summary-content .woocommerce-product-rating .comment-form-rating .stars a, 
	#wcf-quick-view-content .summary-content .woocommerce-product-rating .star-rating::before{
	    color: {$primary_color};
	}
	.wcf-bump-order-wrap.wcf-before-checkout .wcf-bump-order-field-wrap input[type=checkbox]:checked:before, wcf-bump-order-wrap.wcf-after-customer .wcf-bump-order-field-wrap input[type=checkbox]:checked:before, wcf-bump-order-wrap.wcf-before-checkout .wcf-bump-order-field-wrap input[type=checkbox]:checked:before, wcf-product-option-wrap .wcf-qty-row div [type='checkbox']:checked:before {
		color: {$primary_color};
	}
	.wcf-product-option-wrap .wcf-qty-row input[type=radio]:checked:before{
		background-color:{$primary_color};
	}
	.wcf-bump-order-wrap.wcf-before-checkout .wcf-bump-order-field-wrap input[type=checkbox]:focus,
	.wcf-bump-order-wrap.wcf-after-customer .wcf-bump-order-field-wrap input[type=checkbox]:focus,
	.wcf-bump-order-wrap.wcf-before-checkout .wcf-bump-order-field-wrap input[type=checkbox]:focus,
	.wcf-product-option-wrap .wcf-qty-row div [type='checkbox']:focus,
	.wcf-product-option-wrap .wcf-qty-row div [type='radio']:checked:focus,
	.wcf-product-option-wrap .wcf-qty-row div [type='radio']:not(:checked):focus{
		border-color: {$primary_color};
		box-shadow: 0 0 2px rgba( " . $r . ', ' . $g . ', ' . $b . ", .8);
	}
	.woocommerce-checkout #your_products_heading{
		color: {$section_heading_color};
		font-family: {$heading_font_family};
	    font-weight: {$heading_font_weight};
	}

	img.emoji, img.wp-smiley {}
	";

/* Add highlight product css to your order table when product option is enabled*/
if ( 'yes' == $enable_product_options ) {

	$output .= "
	.wcf-embed-checkout-form .wcf-yp-skin-classic .wcf-qty-options,
	.wcf-embed-checkout-form .wcf-yp-skin-cards .wcf-qty-row {
		background-color:{$yp_bg_color};
		color:{$yp_text_color};
	}
	
	.wcf-embed-checkout-form .wcf-qty-options .wcf-highlight {
		color:{$yp_hl_text_color};
	  	background-color: {$yp_hl_bg_color};
	  	border-color: {$yp_hl_border_color};
	}
	.wcf-embed-checkout-form .wcf-qty-options .wcf-highlight-head{
		background: {$yp_flag_bg_color};
		color: {$yp_flag_text_color};
		font-family: {$input_font_family};
	}
	
	/* Add css to your order table when variation is enabled*/
	/*.wcf-embed-checkout-form table.shop_table td:first-child,
	.wcf-embed-checkout-form table.shop_table th:first-child{
	    text-align: left;
	}
	.wcf-embed-checkout-form table.shop_table td:last-child,
	.wcf-embed-checkout-form table.shop_table th:last-child{
	    text-align: right;
	}*/
	img.emoji, img.wp-smiley {}";
}

/* For two Step Layout start */

if ( 'two-step' === $checkout_layout ) {
	$output .= "
		.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note{
		    border-color: {$two_step_box_bg_color};
		    background-color: {$two_step_box_bg_color};
		    color: {$two_step_box_text_color};
		}
		.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note:before{
			border-top-color:{$two_step_box_bg_color};
		}
		.wcf-embed-checkout-form-two-step{
			max-width: {$step_two_width}px;
		}
		.wcf-embed-checkout-form-two-step .woocommerce{
			border-left-style:{$two_step_section_border};
		    border-right-style:{$two_step_section_border};
		    border-bottom-style:{$two_step_section_border};
		}
		.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-nav{
			border-top-style: {$two_step_section_border};
			border-left-style: {$two_step_section_border};
			border-right-style: {$two_step_section_border};
		}
		.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-steps .wcf-current .step-name{
			color:{$primary_color};
		}
		.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-steps .steps.wcf-current:before{
			background-color: {$primary_color};
		}
		.wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns a.wcf-next-button{
			color: {$submit_color};
			background: {$submit_bg_color};
			padding-top: {$submit_tb_padding}px;
			padding-bottom: {$submit_tb_padding}px;
			padding-left: {$submit_lr_padding}px;
			padding-right: {$submit_lr_padding}px;
			border-color: {$submit_border_color};
			min-height: {$submit_button_height};
			font-family: {$button_font_family};
		    font-weight: {$button_font_weight};
		}
		.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-nav-btns .wcf-next-button:hover{
			color: {$submit_hover_color};
			background-color: {$submit_bg_hover_color};
			border-color: {$submit_border_hover_color};
		}
		.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-steps .wcf-current .step-name{
			color: {$section_heading_color};
		}
	";
}
/* For two Step Layout close */


/* For pre-checout offer start */

if ( 'yes' === $is_pre_checkut_upsell ) {

	$output .= '
		/* Pre Checkout upsell */
	';

	$output .= "
		.wcf-pre-checkout-offer-wrapper.wcf-pre-checkout-full-width{
			background:{$pre_checkout_bg_color};
		}
		.wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-modal{
			font-family:{$base_font_family};
		}
		.wcf-pre-checkout-offer-wrapper .wcf-nav-bar-step.active .wcf-progress-nav-step{
			background: {$primary_color};
		}
		.wcf-pre-checkout-offer-wrapper .wcf-nav-bar-step.active .wcf-nav-bar-step-line:before, 
		.wcf-pre-checkout-offer-wrapper .wcf-nav-bar-step.active .wcf-nav-bar-step-line:after{
			background: {$primary_color};
		}
		.wcf-pre-checkout-offer-wrapper .wcf-content-main-head .wcf-content-modal-title .wcf_first_name{
			color:{$primary_color};
		}
		.wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-content button.wcf-pre-checkout-offer-btn{
			border-color: {$primary_color};
			background:{$primary_color};	
		}
		.wcf-pre-checkout-offer-wrapper .wcf-nav-bar-step.active .wcf-nav-bar-title:before{
			color: {$primary_color};
		}
	";

	$output .= '
		/* Pre Checkout upsell */
	';
}
/* For pre-checout offer close */
