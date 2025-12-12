/**
 * Jest setup file for SearchWiz React tests.
 *
 * NOTE: This file is used ONLY for JavaScript unit testing with Jest.
 * It is NOT included in the production plugin build.
 * The mock values below (ajaxUrl, nonce, etc.) are test fixtures
 * that simulate WordPress globals during testing.
 *
 * In production, these values are properly localized via wp_localize_script()
 * in class-sw-ajax-search.php which uses admin_url('admin-ajax.php').
 */

// Import jest-dom matchers
import '@testing-library/jest-dom';

// Mock WordPress globals
global.wp = {
	i18n: {
		__: ( text ) => text,
		_n: ( single, plural, number ) => ( number === 1 ? single : plural ),
		_x: ( text ) => text,
		sprintf: ( format, ...args ) => {
			let i = 0;
			return format.replace( /%s/g, () => args[ i++ ] );
		},
	},
};

/**
 * Mock window.searchwizSettings for testing purposes.
 * These are TEST FIXTURES - not production values.
 * In production, these are set via wp_localize_script() in PHP.
 */
global.searchwizSettings = {
	ajaxUrl: '/wp-admin/admin-ajax.php', // Mock URL for testing - production uses admin_url()
	nonce: 'test-nonce-123', // Mock nonce for testing - production uses wp_create_nonce()
	restUrl: '/wp-json/searchwiz/v1/', // Mock REST URL for testing
	pluginUrl: '/wp-content/plugins/searchwiz/', // Mock plugin URL for testing
};

// Suppress console errors during tests (optional)
// const originalError = console.error;
// beforeAll(() => {
//     console.error = (...args) => {
//         if (typeof args[0] === 'string' && args[0].includes('Warning:')) {
//             return;
//         }
//         originalError.call(console, ...args);
//     };
// });
// afterAll(() => {
//     console.error = originalError;
// });
