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
import { useBlockProps } from '@wordpress/block-editor';

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
	const { tlds } = attributes;

	return (
		<div { ...useBlockProps() }>
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
					{__('Σημείωση: Αυτή είναι μια προεπισκόπηση.', 'wp-domain-search')}
				</p>
			</div>
		</div>
	);
}
