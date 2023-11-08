// my-block.js

( function( blocks, editor, element, components ) {
    var el = element.createElement;
    var TextControl = components.TextControl;

    blocks.registerBlockType( 'fernet-encryption/fernet-encrypted-content-block', {
        title: 'Fernet Encrypted Content Block',
        icon: 'universal-access-alt',
        category: 'common',
        
        edit: function( props ) {
            return el(
                TextControl,
                {
                    label: 'Enter your message',
                    value: props.attributes.message,
                    onChange: function( content ) {
                        props.setAttributes( { message: content } );
                    },
                }
            );
        },

        save: function( props ) {
            return el(
                'p',
                null,
                props.attributes.message
            );
        },
    } );
} )(
    window.wp.blocks,
    window.wp.editor,
    window.wp.element,
    window.wp.components
);
