import generateCSS from "../../../dist/blocks/controls/generate-css"
import generateCSSUnit from "../../../dist/blocks/controls/generate-css-unit"

function styling( props ) {
	const {
        productBorderColor,
        productBorderRadius,
        productBorderWidth,
        productBorderStyle,
        productTextBgColor,
        productTextColor,
        productTitleTextColor,
        //orderbumpBorderColor,
        //orderbumpBorderRadius,
        //orderbumpBorderWidth,
        //orderbumpBorderStyle,
        //orderbumpTextBgColor,
        //orderbumpTextColor,
        //orderbumpcontentTextColor,
        //orderbumpcontentBgColor,
        twoStepBgColor,
        twoStepTextColor,
        // Text
        tstextFontFamily,
        tstextFontWeight,
        tstextFontSubset,
        tstextFontSize,
        tstextFontSizeType,
        tstextFontSizeMobile,
        tstextFontSizeTablet,
        tstextLineHeight,
        tstextLineHeightType,
        tstextLineHeightMobile,
        tstextLineHeightTablet,
    } = props.attributes

    var tablet_selectors = {}
    var mobile_selectors = {}

	var selectors = {

        " .wcf-product-option-wrap .wcf-qty-options .wcf-qty-row" : {
            "color":productTextColor,
        },
        " .wcf-product-option-wrap.wcf-yp-skin-classic .wcf-qty-options, .wcf-product-option-wrap.wcf-yp-skin-cards .wcf-qty-options .wcf-qty-row" : {
            "background-color" : productTextBgColor,
            "border-style" : productBorderStyle,
            "border-width" : generateCSSUnit( productBorderWidth,'px' ),
            "border-color" : productBorderColor,
            "border-radius":  generateCSSUnit( productBorderRadius,'px' ),
        },
        " .wcf-product-option-wrap #your_products_heading" : {
            "color": productTitleTextColor,
        },
        //" .wcf-bump-order-wrap" : {
        //    "background-color" :  orderbumpTextBgColor,
        //    "border-style" :  orderbumpBorderStyle,
        //    "border-radius":  generateCSSUnit(  orderbumpBorderRadius,'px' ),
        //    "border-width" : generateCSSUnit(  orderbumpBorderWidth,'px' ),
        //    "border-color" :  orderbumpBorderColor,
        //},
        //" .wcf-bump-order-wrap .wcf-bump-order-field-wrap, .wcf-bump-order-wrap" : {
        //    "border-width" : generateCSSUnit(  orderbumpBorderWidth,'px' ),
        //    "border-color" :  orderbumpBorderColor,
        //},        
        //" .wcf-bump-order-style-2 .wcf-bump-order-field-wrap, .wcf-bump-order-style-1 .wcf-content-container" : {
        //    "border-top-style" :  orderbumpBorderStyle,
        //    "border-width" : generateCSSUnit(  1,'px' ),
        //    "border-color" :  orderbumpBorderColor,
        //},        
        //" .wcf-bump-order-wrap .wcf-bump-order-field-wrap, #payment .wcf-bump-order-wrap .wcf-bump-order-field-wrap .wcf-bump-order-label" : {
        //    "background-color":  orderbumpTextBgColor,
        //    "color" : orderbumpTextColor,
        //},
        //" .wcf-bump-order-content" : {
        //    "color" : orderbumpcontentTextColor,
        //    "background-color":  orderbumpcontentBgColor,
        //},
        " .wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note" : {
            "color": twoStepTextColor,
            "background-color":twoStepBgColor+" !important",
            "border-color": twoStepBgColor,
            "font-family"     : tstextFontFamily,
			"font-weight"     : tstextFontWeight,
			"font-size"       : generateCSSUnit( tstextFontSize, tstextFontSizeType ),
            "line-height"     : generateCSSUnit( tstextLineHeight, tstextLineHeightType ),
        },        
        " .wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note:before" : {
            "border-top-color": twoStepBgColor,
        },
    }

    tablet_selectors[" .wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note"] = {
        "font-size"       : generateCSSUnit( tstextFontSizeTablet, tstextFontSizeType ),
        "line-height"     : generateCSSUnit( tstextLineHeightTablet, tstextLineHeightType ),
    }
    
    mobile_selectors[" .wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note"] = {
        "font-size"       : generateCSSUnit( tstextFontSizeMobile, tstextFontSizeType ),
        "line-height"     : generateCSSUnit( tstextLineHeightMobile, tstextLineHeightType ),
	}

    var base_selector = `.cf-block-${ props.clientId.substr( 0, 8 ) }`
    
    var styling_css = generateCSS( selectors, base_selector)

    styling_css += generateCSS( tablet_selectors, base_selector, true, "tablet" )

	styling_css += generateCSS( mobile_selectors, base_selector, true, "mobile" )
    
    return styling_css
    
    }
    
    export default styling