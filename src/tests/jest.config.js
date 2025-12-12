/**
 * Jest configuration for SearchWiz React tests.
 *
 * @see https://jestjs.io/docs/configuration
 */
module.exports = {
	// Use WordPress scripts preset as base
	...require( '@wordpress/scripts/config/jest-unit.config' ),

	// Test files location - React component tests and Functional tests
	testMatch: [
		'<rootDir>/react/**/*.test.js',
		'<rootDir>/react/**/*.test.jsx',
		'<rootDir>/Functional/**/*.test.js',
		'<rootDir>/Functional/**/*.test.jsx',
	],

	// Setup files (jest.setup.js is in the same directory as this config)
	setupFilesAfterEnv: [ '<rootDir>/jest.setup.js' ],

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
