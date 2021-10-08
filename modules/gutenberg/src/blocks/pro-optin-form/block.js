const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { Fragment } = wp.element;
const { InspectorControls, ColorPalette } = wp.blockEditor;
const { PanelBody,
	SelectControl,
 } = wp.components;
 const { select, withSelect } = wp.data;

const { __ } = wp.i18n;

const OptinProFilterBlocks =  createHigherOrderComponent( ( BlockEdit )  => {

    return ( props ) => {

        const {
            attributes: {
                input_skins,
            },   
            setAttributes                                
            } = props

        let getBlockName = select('core/block-editor').getBlockName( props.clientId );

        const input_field_skins = () => {		
            if( 'wcfb/optin-form' === getBlockName ){
                return (
                    <PanelBody title={ __( "Input Field Skins", 'cartflows-pro' ) } initialOpen={ false }>

                        <SelectControl
                            label={ __( "Style" ) }
                            value={ input_skins }
                            onChange={ ( value ) => setAttributes( { input_skins: value } ) }
                            options={ [
                                { value: "deafult", label: __( "Default", 'cartflows-pro' ) },
                                { value: "floating-labels", label: __( "Floating Labels", 'cartflows-pro' ) },
                            ] }
                        />

                    </PanelBody>
                )
            }}

        var input_field = document.getElementById('wcf-optin-form');

        if ( input_field !== null ) {
            if ( input_skins === 'floating-labels' ) {
                input_field.classList.remove("wcf-field-default");
                input_field.classList.add("wcf-field-floating-labels");
            }else{
                input_field.classList.remove("wcf-field-floating-labels");
                input_field.classList.add("wcf-field-default");
            }
        }

        return (
            <Fragment>
                <BlockEdit { ...props } />
                <InspectorControls>
                    {/* {input_field_skins()} */}
                </InspectorControls>
            </Fragment>
        );
            
	}
}, "OptinProFilterBlocks" );

if( 'optin' === cfp_blocks_info.step_type && cfp_blocks_info.is_woo_active){
    addFilter(
        'editor.BlockEdit',
        'optin/cfp-optin-filter-blocks',
        OptinProFilterBlocks
    );
}