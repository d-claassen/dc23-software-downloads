/**
 * WordPress dependencies.
 */
const { registerPlugin } = require( '@wordpress/plugins' );

/**
 * Internal dependencies.
 */
import { SidebarPanel } from './sidebar-panel';


/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * All files containing `style` keyword are bundled together. The code used
 * gets applied both to the front of your site and to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './style.scss';

registerPlugin( 'dc23-software-downloads', { render: SidebarPanel } );
