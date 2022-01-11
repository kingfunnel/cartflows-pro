<?php
/**
 * Checkout Form Module extend front-end CSS php file.
 *
 * @package Checkout Form Module
 */

global $post;


if ( 'yes' === $module_settings->pre_checkout_enable_preview ) { ?>
	body.fl-builder-edit .wcf-pre-checkout-offer-wrapper.wcf-pre-checkout-full-width {
		visibility: visible;
		opacity: 1;
		text-align: center;
		position: absolute;
		width: 100%;
		height: 100%;
		left: 0;
		top: 0;
		padding: 30px;
		z-index: 100009!important;
	}
<?php } ?>

.fl-lightbox .cartflows-bb-note {
	color: #007cba;
	font-weight: 500;
}

<?php
	$checkout_layout    = $module_settings->checkout_layout;
	$product_options    = get_post_meta( $post->ID, 'wcf-enable-product-options', true );
	$pre_checkout_offer = get_post_meta( $post->ID, 'wcf-pre-checkout-offer', true );
?>

<?php /* Two step */ ?>
<?php if ( 'two-step' === $checkout_layout ) { ?>

	.fl-node-<?php echo $module_id; ?> .wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note {
		color: <?php echo FLBuilderColor::hex_or_rgb( $module_settings->note_text_color ); ?>;
	}
	.fl-node-<?php echo $module_id; ?> .wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note {
		background-color: <?php echo FLBuilderColor::hex_or_rgb( $module_settings->note_bg_color ); ?>;
	}
	.fl-node-<?php echo $module_id; ?> .wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note:before {
		border-top-color: <?php echo FLBuilderColor::hex_or_rgb( $module_settings->note_bg_color ); ?>;
	}

	<?php
	if ( class_exists( 'FLBuilderCSS' ) ) {
		FLBuilderCSS::typography_field_rule(
			array(
				'settings'     => $module_settings,
				'setting_name' => 'note_typography',
				'selector'     => ".fl-node-$module_id .wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note",
			)
		);
	}
	?>

<?php } ?>

<?php /* Product options. */ ?>
<?php if ( 'yes' === $product_options ) { ?>

	.fl-node-<?php echo $module_id; ?> .cartflows-bb__checkout-form .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row {
		color: <?php echo FLBuilderColor::hex_or_rgb( $module_settings->product_text_color ); ?>;
	}
	.fl-node-<?php echo $module_id; ?> .cartflows-bb__checkout-form .wcf-product-option-wrap.wcf-yp-skin-classic .wcf-qty-options,
	.fl-node-<?php echo $module_id; ?> .cartflows-bb__checkout-form .wcf-product-option-wrap.wcf-yp-skin-cards .wcf-qty-options .wcf-qty-row {
		background-color: <?php echo FLBuilderColor::hex_or_rgb( $module_settings->product_bg_color ); ?>;
	}
	.fl-node-<?php echo $module_id; ?> .cartflows-bb__checkout-form .wcf-product-option-wrap.wcf-yp-skin-classic .wcf-qty-options,
	.fl-node-<?php echo $module_id; ?> .cartflows-bb__checkout-form .wcf-product-option-wrap.wcf-yp-skin-cards .wcf-qty-options .wcf-qty-row {
		border-radius: <?php echo ( '' != $module_settings->product_option_border_radius ) ? $module_settings->product_option_border_radius : '0'; ?>px;
	}
	<?php if ( 'none' !== $module_settings->product_option_border_style ) { ?>
	.fl-node-<?php echo $module_id; ?> .cartflows-bb__checkout-form .wcf-product-option-wrap.wcf-yp-skin-classic .wcf-qty-options {
		border-style: <?php echo ( '' != $module_settings->product_option_border_style ) ? $module_settings->product_option_border_style : 'solid'; ?>;
		border-color: <?php echo FLBuilderColor::hex_or_rgb( $module_settings->product_option_border_color ); ?>;
		border-width: <?php echo ( '' != $module_settings->product_option_border_size ) ? $module_settings->product_option_border_size : '0'; ?>px;
	}

	.fl-node-<?php echo $module_id; ?> .cartflows-bb__checkout-form .wcf-product-option-wrap.wcf-yp-skin-cards .wcf-qty-options .wcf-qty-row {
		border-style: <?php echo ( '' != $module_settings->product_option_border_style ) ? $module_settings->product_option_border_style : 'solid'; ?>;
		border-color: <?php echo FLBuilderColor::hex_or_rgb( $module_settings->product_option_border_color ); ?>;
		border-width: <?php echo ( '' != $module_settings->product_option_border_size ) ? $module_settings->product_option_border_size : '0'; ?>px;
	}

	<?php } ?>

	.fl-node-<?php echo $module_id; ?> .cartflows-bb__checkout-form .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row.wcf-highlight {
		color: <?php echo FLBuilderColor::hex_or_rgb( $module_settings->highlight_product_text_color ); ?>;
	}
	.fl-node-<?php echo $module_id; ?> .cartflows-bb__checkout-form .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row.wcf-highlight {
		background-color: <?php echo FLBuilderColor::hex_or_rgb( $module_settings->highlight_product_bg_color ); ?>;
	}
	.fl-node-<?php echo $module_id; ?> .cartflows-bb__checkout-form .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row.wcf-highlight {
		border-radius: <?php echo ( '' != $module_settings->highlight_product_border_radius ) ? $module_settings->highlight_product_border_radius : '0'; ?>px;
	}
		<?php if ( 'none' != $module_settings->highlight_product_border_style ) { ?>
		.fl-node-<?php echo $module_id; ?> .cartflows-bb__checkout-form .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row.wcf-highlight {
			border-style: <?php echo ( '' != $module_settings->highlight_product_border_style ) ? $module_settings->highlight_product_border_style : 'solid'; ?>;
			border-width: <?php echo ( '' != $module_settings->highlight_product_border_size ) ? $module_settings->highlight_product_border_size : '0'; ?>px;
			border-color: <?php echo FLBuilderColor::hex_or_rgb( $module_settings->highlight_product_border_color ); ?>;
		}
	<?php } ?>

	.fl-node-<?php echo $module_id; ?> .cartflows-bb__checkout-form .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row.wcf-highlight .wcf-highlight-head {
		color: <?php echo FLBuilderColor::hex_or_rgb( $module_settings->highlight_flag_text_color ); ?>;
	}
	.fl-node-<?php echo $module_id; ?> .cartflows-bb__checkout-form .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row.wcf-highlight .wcf-highlight-head {
		background-color: <?php echo FLBuilderColor::hex_or_rgb( $module_settings->highlight_flag_bg_color ); ?>;
	}

<?php } ?>


<?php // Pre Checkout. ?>
<?php if ( 'yes' === $pre_checkout_offer ) { ?>

	body .wcf-pre-checkout-offer-wrapper .wcf-pre-checkout-offer-price,
	body .wcf-pre-checkout-offer-wrapper .wcf-progress-bar-nav,
	body .wcf-pre-checkout-offer-wrapper .wcf-pre-checkout-offer-desc span,
	body .wcf-pre-checkout-offer-wrapper .wcf-pre-checkout-skip-btn .wcf-pre-checkout-skip {
		color: <?php echo FLBuilderColor::hex_or_rgb( $module_settings->pre_checkout_desc_color ); ?> !important;
	}

	body .wcf-pre-checkout-offer-wrapper .wcf-lightbox-content .wcf-content-main-head .wcf-content-modal-sub-title span,
	body .wcf-pre-checkout-offer-wrapper .wcf-content-modal-sub-title span {
		color: <?php echo FLBuilderColor::hex_or_rgb( $module_settings->pre_checkout_sub_title_color ); ?> !important;
	}

	body .wcf-pre-checkout-offer-wrapper .wcf-lightbox-content .wcf-content-main-head .wcf-content-modal-title h1,
	body .wcf-pre-checkout-offer-wrapper .wcf-lightbox-content #wcf-pre-checkout-offer-content .wcf-pre-checkout-info .wcf-pre-checkout-offer-product-title h1,
	body .wcf-pre-checkout-offer-wrapper .wcf-content-main-head .wcf-content-modal-title .wcf_first_name {
		color: <?php echo FLBuilderColor::hex_or_rgb( $module_settings->pre_checkout_title_color ); ?> !important;
	}

	body .wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-modal,
	body .wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-content {
		background-color: <?php echo FLBuilderColor::hex_or_rgb( $module_settings->pre_checkout_bg_color ); ?>;
	}
	body .wcf-pre-checkout-offer-wrapper.wcf-pre-checkout-full-width {
		background-color: <?php echo FLBuilderColor::hex_or_rgb( $module_settings->pre_checkout_overlay_bg_color ); ?>;
	}

<?php } ?>
