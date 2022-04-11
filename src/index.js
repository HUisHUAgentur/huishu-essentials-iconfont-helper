import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import './index.css';
import metadata from '../block.json';
import Edit from './edit';

registerBlockType( metadata, {
	edit: Edit,
    save: ( props ) => {
		let iconclass = "icon-" + props.attributes.selectedIcon;
		let href = props.attributes.url;
		const blockProps = useBlockProps.save({
			className: 'huishu-icon-block',
		});
		let targetstring = !!props.attributes.targetblank ? '_blank' : '';
		return <div { ...blockProps } >
			{ href ? 
				<a href = { href } 
					target = { !!props.attributes.targetblank && targetstring }
					rel = { !!props.attributes.targetblank && 'noopener noreferrer'}
				>
					<i className = { iconclass }></i>		
				</a>
				:
				<i className = { iconclass }></i>
			}
		</div>
	}
} );