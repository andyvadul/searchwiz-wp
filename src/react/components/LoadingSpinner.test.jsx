/**
 * Tests for LoadingSpinner Component
 */

import { render, screen } from '@testing-library/react';
import LoadingSpinner from './LoadingSpinner';

describe( 'LoadingSpinner', () => {
	test( 'renders loading spinner container', () => {
		render( <LoadingSpinner /> );

		const container = document.querySelector( '.searchwiz-loading' );
		expect( container ).toBeInTheDocument();
	} );

	test( 'renders spinner element', () => {
		render( <LoadingSpinner /> );

		const spinner = document.querySelector( '.spinner' );
		expect( spinner ).toBeInTheDocument();
	} );

	test( 'displays "Searching..." text', () => {
		render( <LoadingSpinner /> );

		expect( screen.getByText( 'Searching...' ) ).toBeInTheDocument();
	} );
} );
