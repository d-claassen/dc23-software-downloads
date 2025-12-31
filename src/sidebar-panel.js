/**
 * WordPress dependencies.
 */
import {
	Button,
	FormToggle,
	FormTokenField,
	TextControl,
	__experimentalToolsPanel as ToolsPanel,
	__experimentalToolsPanelItem as ToolsPanelItem,
	__experimentalVStack as VStack,
} from '@wordpress/components';
import { useEntityProp } from '@wordpress/core-data';
import { dateI18n, getDate } from '@wordpress/date';
import { useMemo } from '@wordpress/element';
import { filterURLForDisplay } from '@wordpress/url';
const { useSelect } = require( '@wordpress/data' );
const { PluginDocumentSettingPanel } = require( '@wordpress/editor' );

/**
 * Internal dependencies.
 */


/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
// import './style.scss';

function SidebarContent( { postId, postType } ) {
	console.log( 'content panel', { postId, postType } );
	
	const [ meta, updateMeta ] = useEntityProp(
		'postType',
		postType,
		'meta',
		postId
	);

	console.log( 'meta panel', { postId, postType, meta } );

	const oldMeta = useMemo( () => {
		return meta;
	}, [ postType, postId ] );
	

	console.log( 'memo meta panel', { postId, postType, meta, oldMeta } );
	
	return (
		<VStack spacing={ 1 }>
			
		</VStack>
	);
}

export const SidebarPanel = () => {
	const { postId, postType } = useSelect( ( select ) => {
		const { getCurrentPostId, getCurrentPostType } =
			select( 'core/editor' );

		return {
			postId: getCurrentPostId(),
			postType: getCurrentPostType(),
		};
	}, [] );

	if ( 'download' !== postType ) {
		console.log( 'no panel', { postId, postType } );
		return null;
	}

	console.log( 'yes panel', { postId, postType } );

	return (
		<PluginDocumentSettingPanel
			name="dc23-software-downloads"
			title="Software Downloads"
		>
			<SidebarContent
				postId={ postId }
				postType={ postType }
			/>
		</PluginDocumentSettingPanel>
	);
};


