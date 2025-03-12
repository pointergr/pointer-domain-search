/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, Disabled } from '@wordpress/components';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
	const { username, password, tlds } = attributes;

	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<PanelBody title={__('Ρυθμίσεις API', 'wp-domain-search')}>
					<TextControl
						label={__('Username', 'wp-domain-search')}
						value={username}
						onChange={(value) => setAttributes({ username: value })}
						help={__('Εισάγετε το username σας από την υπηρεσία Pointer.gr', 'wp-domain-search')}
					/>
					<TextControl
						label={__('Password', 'wp-domain-search')}
						value={password}
						type="password"
						onChange={(value) => setAttributes({ password: value })}
						help={__('Εισάγετε το password σας από την υπηρεσία Pointer.gr', 'wp-domain-search')}
					/>
					<TextControl
						label={__('TLDs', 'wp-domain-search')}
						value={tlds}
						onChange={(value) => setAttributes({ tlds: value })}
						help={__('Εισάγετε τα TLDs διαχωρισμένα με | (π.χ. gr|com|net)', 'wp-domain-search')}
					/>
				</PanelBody>
			</InspectorControls>

			<div className="wp-domain-search-preview">
				<h4>{__('Προεπισκόπηση Αναζήτησης Domain', 'wp-domain-search')}</h4>
				<div className="wp-domain-search-form">
					<div className="wp-domain-search-input-wrap">
						<input
							type="text"
							className="wp-domain-search-input"
							placeholder={__('Εισάγετε όνομα domain...', 'wp-domain-search')}
							disabled
						/>
						<button
							className="wp-domain-search-button"
							disabled
						>
							{__('Αναζήτηση', 'wp-domain-search')}
						</button>
					</div>
					<div className="wp-domain-search-tlds">
						{tlds.split('|').map((tld, index) => (
							<label key={tld} className="wp-domain-search-tld-label">
								<input
									type="checkbox"
									checked={index === 0}
									disabled
								/>
								.{tld}
							</label>
						))}
					</div>
				</div>
				<p className="wp-domain-search-note">
					{__('Σημείωση: Αυτή είναι μια προεπισκόπηση. Η πραγματική αναζήτηση θα λειτουργήσει στην προβολή της σελίδας.', 'wp-domain-search')}
				</p>
			</div>
		</div>
	);
}
