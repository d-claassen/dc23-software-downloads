/**
 * WordPress dependencies.
 */
import { useBlockProps } from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';

/**
 * Internal dependencies.
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @param {Object} props
 * @param {Object} props.context
 * @param {string} props.context.postType
 * @param {number} props.context.postId
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
function Content( { context: { postType, postId } } ) {
	const [ meta ] = useEntityProp( 'postType', postType, 'meta', postId );

	const os = meta?._dc23_software_os;

	return (
		<div { ...useBlockProps() }>
            { os }
		</div>
	);
}

function Placeholder() {
	return (
		<div { ...useBlockProps() }>
            Windows
		</div>
	);
}

export default function Edit( { context } ) {
	const { postType, postId } = context;

	return (
		<>
			{ postId && postType ? (
				<Content context={ context } />
			) : (
				<Placeholder />
			) }
		</>
	);
}
