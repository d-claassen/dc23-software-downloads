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
const { registerPlugin } = require( '@wordpress/plugins' );

/**
 * Internal dependencies.
 */
import { DropdownDateTimePicker } from './lib/components/dropdown-date-time-picker';
import { DropdownPostSelect } from './lib/components/dropdown-post-select';
import { DropdownUrl } from './lib/components/dropdown-url';
import { PostPanelRow } from './lib/components/post-panel-row';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './style.scss';

const SUPPORTED_EVENT_ROLES = [
	'Attending',
	'Organizing',
	'Sponsoring',
	'Performing',
];

const DC23TeaExtendedPanel = () => {
	const { postId, postType, organizers, venues } = useSelect( ( select ) => {
		const { getCurrentPostId, getCurrentPostType } =
			select( 'core/editor' );
		const { getEntityRecords } = select( 'core' );

		return {
			postId: getCurrentPostId(),
			postType: getCurrentPostType(),
			organizers: getEntityRecords( 'postType', 'tribe_organizer' ),
			venues: getEntityRecords( 'postType', 'tribe_venue' ),
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

	if ( 'tribe_events' !== postType ) {
		return null;
	}

	const {
		_EventAllDay, // boolean
		_EventStartDate, // string, YYYY-MM-DD HH:mm:ss, local tz
		// _EventStartDateUTC, // string, YYYY-MM-DD HH:mm:ss, UTC
		_EventEndDate, // string, YYYY-MM-DD HH:mm:ss, local tz
		// _EventEndDateUTC, // string, YYYY-MM-DD HH:mm:ss, UTC
		_EventDateTimeSeparator, // string
		_EventTimeRangeSeparator, // string
		_EventURL, // string
		_EventOrganizerID, // number (array?), post ID
		_EventVenueID, // number (array), post ID
		_EventRole, // string array
	} = meta;

	const startDate = getDate( _EventStartDate );
	const startLabel = _EventAllDay
		? dateI18n( 'F j, Y', startDate )
		: dateI18n( 'F j, Y H:i', startDate );

	const endDate = getDate( _EventEndDate );
	const endLabel = _EventAllDay
		? dateI18n( 'F j, Y', endDate )
		: dateI18n( 'F j, Y H:i', endDate );

	let organizerTitle = '';
	if ( organizers ) {
		const selectedOrganizer = organizers.find( ( organizer ) => {
			return _EventOrganizerID.includes( organizer.id );
		} );

		organizerTitle = selectedOrganizer?.title.rendered;
	}

	let venueTitle = '';
	if ( venues ) {
		const selectedVenue = venues.find( ( venue ) => {
			return _EventVenueID.includes( venue.id );
		} );

		venueTitle = selectedVenue?.title.rendered;
	}

	return (
		<PluginDocumentSettingPanel
			name="dc23-tea-extended-panel"
			title="The Event Attendee"
		>
			<VStack spacing={ 1 }>
				<PostPanelRow label="Is all day event" className="">
					<Button
						variant="tertiary"
						onClick={ () =>
							updateMeta( {
								...meta,
								_EventAllDay: ! _EventAllDay,
							} )
						}
					>
						<FormToggle
							checked={ _EventAllDay }
							// Disable tab focus and mouse events to make the button the interaction point.
							style={ { pointerEvents: 'none' } }
							tabIndex={ -1 }
						/>
					</Button>
				</PostPanelRow>
				<PostPanelRow label="Start date" className="">
					<DropdownDateTimePicker
						date={ _EventStartDate }
						setDate={ ( newDate ) =>
							updateMeta( { ...meta, _EventStartDate: newDate } )
						}
						buttonLabel={ startLabel }
						hasTimePicker={ ! _EventAllDay }
					/>
				</PostPanelRow>

				<PostPanelRow label="End date" className="">
					<DropdownDateTimePicker
						date={ _EventEndDate }
						setDate={ ( newDate ) =>
							updateMeta( { ...meta, _EventEndDate: newDate } )
						}
						buttonLabel={ endLabel }
						hasTimePicker={ ! _EventAllDay }
					/>
				</PostPanelRow>

				<PostPanelRow label="Website" className="">
					<DropdownUrl
						url={ _EventURL }
						onChange={ ( newUrl ) =>
							updateMeta( { ...meta, _EventURL: newUrl } )
						}
						buttonLabel={ filterURLForDisplay( _EventURL ) }
						inputLabel="Event website"
					/>
				</PostPanelRow>

				<PostPanelRow label="Organizer">
					<DropdownPostSelect
						buttonLabel={ organizerTitle }
						inputLabel="Select organizer"
						value={ _EventOrganizerID }
						options={ organizers }
						onChange={ ( postID ) => {
							updateMeta( {
								...meta,
								_EventOrganizerID: [ postID ],
							} );
						} }
					/>
				</PostPanelRow>

				<PostPanelRow label="Venue">
					<DropdownPostSelect
						buttonLabel={ venueTitle }
						inputLabel="Select venue"
						value={ _EventVenueID }
						options={ venues }
						onChange={ ( postID ) => {
							updateMeta( {
								...meta,
								_EventVenueID: [ postID ],
							} );
						} }
					/>
				</PostPanelRow>
			</VStack>

			<ToolsPanel
				label="Advanced date"
				resetAll={ () =>
					updateMeta( {
						...meta,
						_EventDateTimeSeparator:
							oldMeta._EventDateTimeSeparator,
						_EventTimeRangeSeparator:
							oldMeta._EventTimeRangeSeparator,
						_EventRole: oldMeta._EventRole,
					} )
				}
			>
				<ToolsPanelItem
					label="Date time separator"
					hasValue={ () =>
						oldMeta._EventDateTimeSeparator !==
						_EventDateTimeSeparator
					}
				>
					<TextControl
						__nextHasNoMarginBottom
						label="Date time separator"
						value={ _EventDateTimeSeparator }
						onChange={ ( separator ) =>
							updateMeta( {
								...meta,
								_EventDateTimeSeparator: separator,
							} )
						}
					/>
				</ToolsPanelItem>

				<ToolsPanelItem
					label="Date range separator"
					hasValue={ () =>
						oldMeta._EventTimeRangeSeparator !==
						_EventTimeRangeSeparator
					}
				>
					<TextControl
						__nextHasNoMarginBottom
						label="Date range separator"
						value={ _EventTimeRangeSeparator }
						onChange={ ( separator ) =>
							updateMeta( {
								...meta,
								_EventTimeRangeSeparator: separator,
							} )
						}
					/>
				</ToolsPanelItem>

				<ToolsPanelItem
					label="Role at event"
					hasValue={ () => oldMeta._EventRole.length > 0 }
				>
					<FormTokenField
						__nextHasNoMarginBottom
						__experimentalExpandOnFocus
						__experimentalValidateInput={ ( token ) =>
							SUPPORTED_EVENT_ROLES.includes( token )
						}
						tokenizeOnBlur
						label="Role at event"
						onChange={ ( roles ) =>
							updateMeta( {
								...meta,
								_EventRole: roles,
							} )
						}
						suggestions={ SUPPORTED_EVENT_ROLES }
						value={ _EventRole }
					/>
				</ToolsPanelItem>
			</ToolsPanel>
		</PluginDocumentSettingPanel>
	);
};

registerPlugin( 'dc23-tea-extended', { render: DC23TeaExtendedPanel } );
