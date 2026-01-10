/**
 * WordPress dependencies.
 */
import { useBlockProps } from '@wordpress/block-editor';
import { useEntityProp } from '@wordpress/core-data';

const CATEGORIES = [
	{ label: 'Game application', value: 'GameApplication' },
	{ label: 'Social networking application', value: 'SocialNetworkingApplication' },
	{ label: 'Travel application', value: 'TravelApplication' },
	{ label: 'Shopping application', value: 'ShoppingApplication' },
	{ label: 'Sports application', value: 'SportsApplication' },
	{ label: 'Lifestyle application', value: 'LifestyleApplication' },
	{ label: 'Business application', value: 'BusinessApplication' },
	{ label: 'Design application', value: 'DesignApplication' },
	{ label: 'Developer application', value: 'DeveloperApplication' },
	{ label: 'Driver application', value: 'DriverApplication' },
	{ label: 'Educational application', value: 'EducationalApplication' },
	{ label: 'Health application', value: 'HealthApplication' },
	{ label: 'Finance application', value: 'FinanceApplication' },
	{ label: 'Security application', value: 'SecurityApplication' },
	{ label: 'Browser application', value: 'BrowserApplication' },
	{ label: 'Communication application', value: 'CommunicationApplication' },
	{ label: 'Desktop enhancement application', value: 'DesktopEnhancementApplication' },
	{ label: 'Entertainment application', value: 'EntertainmentApplication' },
	{ label: 'Multimedia application', value: 'MultimediaApplication' },
	{ label: 'Home application', value: 'HomeApplication' },
	{ label: 'Utilities application', value: 'UtilitiesApplication' },
	{ label: 'Reference application', value: 'ReferenceApplication' },
];

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

	const category = meta?._dc23_software_category;
	const label    = CATEGORIES.filter( ({value}) => value === category )?.label;

	return (
		<div { ...useBlockProps() }>
			{ label }
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
