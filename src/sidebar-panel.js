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
import './style.scss';

export const SidebarPanel = () => {
	const { postId, postType } = useSelect( ( select ) => {
		const { getCurrentPostId, getCurrentPostType } =
			select( 'core/editor' );
		// const { getEntityRecords } = select( 'core' );

		return {
			postId: getCurrentPostId(),
			postType: getCurrentPostType(),
		};
	}, [] );

	const [ meta, updateMeta ] = useEntityProp(
		'postType',
		postType,
		'meta',
		postId
	);

	const oldMeta = useMemo( () => {
		// console.log( 'memoize meta' );
		return meta;
	}, [ postType, postId ] );

	if ( 'downloads' !== postType ) {
		return null;
	}


l


	return (
		<PluginDocumentSettingPanel
			name="dc23-software-downloads"
			title="Software Downloads"
		>
			<VStack spacing={ 1 }>
				

			</VStack>
		</PluginDocumentSettingPanel>
	);
};
