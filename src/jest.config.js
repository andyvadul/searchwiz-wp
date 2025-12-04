/**
 * Jest configuration for SearchWiz React tests.
 *
 * @see https://jestjs.io/docs/configuration
 */
module.exports = {
	// Use WordPress scripts preset as base
	...require( '@wordpress/scripts/config/jest-unit.config' ),

	// Test files location
	testMatch: [
		'<rootDir>/react/**/*.test.js',
		'<rootDir>/react/**/*.test.jsx',
	],

	// Setup files
	setupFilesAfterEnv: [ '<rootDir>/tests/jest.setup.js' ],

	// Coverage configuration
	collectCoverageFrom: [
		'react/**/*.{js,jsx}',
		'!react/**/*.test.{js,jsx}',
		'!react/**/index.js',
		'!**/node_modules/**',
	],

	// Coverage thresholds (start at 0, increase as tests are added)
	coverageThreshold: {
		global: {
			branches: 0,
			functions: 0,
			lines: 0,
			statements: 0,
		},
	},

	// Coverage reporters
	coverageReporters: [ 'text', 'lcov', 'html' ],

	// Module name mapper for CSS/assets
	moduleNameMapper: {
		'\\.(css|less|scss|sass)$': 'identity-obj-proxy',
		'\\.(gif|ttf|eot|svg|png)$': '<rootDir>/__mocks__/fileMock.js',
	},

	// Transform ignore patterns
	transformIgnorePatterns: [
		'/node_modules/(?!(@wordpress)/)',
	],

	// Test environment
	testEnvironment: 'jsdom',
};
