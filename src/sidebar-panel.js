/**
 * WordPress dependencies.
 */
import {
	Button,
	FormToggle,
	FormTokenField,
	SelectControl,
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
		return null;
	}

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

function SidebarContent( { postId, postType } ) {
	const [ meta, updateMeta ] = useEntityProp(
		'postType',
		postType,
		'meta',
		postId
	);
	
	const {
		_SoftwareType = '',
		_dc23_software_category: softwareCategory = '',
		_dc23_software_os: softwareOS = '',
	} = meta;

	const oldMeta = useMemo( () => {
		return meta;
	}, [ postType, postId ] );
	
	return (
		<VStack spacing={ 1 }>
			<SelectControl
		  __next40pxDefaultSize
				__nextHasNoMarginBottom
		  label="Software type"
		  labelPosition="top"
		  onChange={(value) => {
					updateMeta({ ...meta, _SoftwareType: value });
				}}
		  options={[
		    {
		      disabled: true,
		      label: 'Select an Option',
		      value: ''
		    },
		    {
		      label: 'Software application',
		      value: 'SoftwareApplication'
		    },
		    {
		      label: 'Mobile application',
		      value: 'MobileApplication'
		    },
		    {
		      label: 'Web application',
		      value: 'WebApplication'
		    }
		  ]}
		  size="default"
				value={_SoftwareType}
		  variant="default"
			/>
			<SoftwareCategorySelectControl
				onChange={(value) => {
					updateMeta({ ...meta, _dc23_software_category: value });
				}}
				value={softwareCategory}
			/>
			<SoftwareOSControl
				onChange={(value) => {
					updateMeta({ ...meta, _dc23_software_os: value });
				}}
				value={softwareOS}
			/>
		</VStack>
	);
}

function SoftwareCategorySelectControl( { value, onChange } ) {
	return (
				<SelectControl
		  __next40pxDefaultSize
				__nextHasNoMarginBottom
		  label="Software category"
		  labelPosition="top"
		  onChange={onChange}
		  options={[
		    {
		      disabled: true,
		      label: 'Select an Option',
		      value: ''
		    },
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
		  ]}
		  size="default"
				value={value}
		  variant="default"
			/>
	);
}

function SoftwareOSControl( { value, onChange } ) {
	return (
				<TextControl
		  __next40pxDefaultSize
				__nextHasNoMarginBottom
		  label="Operating system"
		  labelPosition="top"
		  onChange={onChange}
		  size="default"
				value={value}
		  variant="default"
			/>
	);
}

