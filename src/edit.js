import { InspectorControls, useBlockProps, URLPopover, URLInput } from '@wordpress/block-editor';
import { Fragment, useRef, useState } from '@wordpress/element';
import { Button, PanelBody, SelectControl, ToggleControl } from '@wordpress/components';
import { keyboardReturn } from '@wordpress/icons';
import { __ } from '@wordpress/i18n';


const IconLinkURLPopover = ( {
	url,
	setAttributes,
	setPopover,
	anchorRef,
} ) => (
	<URLPopover
		anchorRef={ anchorRef?.current }
		onClose={ () => setPopover( false ) }
	>
		<form
			className="block-editor-url-popover__link-editor"
			onSubmit={ ( event ) => {
				event.preventDefault();
				setPopover( false );
			} }
		>
			<div className="block-editor-url-input">
				<URLInput
					value={ url }
					onChange={ ( nextURL ) =>
						setAttributes( { url: nextURL } )
					}
					placeholder={ __( 'Enter address' ) }
					disableSuggestions={ true }
				/>
			</div>
			<Button
				label={ __( 'Apply' ) }
				type="submit"
				icon={ keyboardReturn }
			/>
		</form>
	</URLPopover>
);


function Edit(props) {
	const {
		attributes,
		isSelected,
		setAttributes,
	} = props;
	const {
		selectedIcon,
		url,
		targetblank
	} = attributes;
	const icons = hu_ep_ih_icons.icons;
	const blockProps = useBlockProps({
		className: 'huishu-icon-block',
	});
	
	const [ showURLPopover, setPopover ] = useState( false );
	const ref = useRef();

	function onChangeSelectIcon( value ) {
		setAttributes( { selectedIcon: value } )
	}

	function toggleTargetBlank(){
		setAttributes( { targetblank: !targetblank } );
	}

	return [
		<Fragment>
				<InspectorControls>
					<PanelBody 
						title="Icon"
						initialOpen={ true }>
								{icons.length < 2 ? 

								<Fragment>
									<p>Es wurden keine Icons gefunden.</p>
								</Fragment>

								: 

								<Fragment>
									<SelectControl 
										label="Icon auswählen"
										onChange={ onChangeSelectIcon } 
										value={ selectedIcon } 
										options={ icons } 	
									/>
									<ToggleControl
										label="Link in neuem Fenster öffnen"
										checked={ !!targetblank }
										onChange= { toggleTargetBlank }
									/>
								</Fragment>
								}
					</PanelBody>
				</InspectorControls>
				<div { ...blockProps }>
					{ selectedIcon ? 
						<Button ref={ ref } onClick={ () => setPopover( true ) }>
							<i className = { 'icon-'+selectedIcon } />
							{ isSelected && showURLPopover && (
								<IconLinkURLPopover
									url={ url }
									setAttributes={ setAttributes }
									setPopover={ setPopover }
									anchorRef={ ref }
								/>
							) }
						</Button>
						:
						'Bitte wählen Sie ein Icon aus.'
					}
				</div>
			</Fragment>
	]    
}
export default Edit;