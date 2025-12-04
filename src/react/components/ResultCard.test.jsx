/**
 * Tests for ResultCard Component
 */

import { render, screen } from '@testing-library/react';
import ResultCard from './ResultCard';

describe( 'ResultCard', () => {
	const mockResult = {
		id: 123,
		title: 'Test Post Title',
		excerpt: 'This is a test excerpt with some content.',
		url: 'https://example.com/test-post',
		thumbnail: 'https://example.com/image.jpg',
		type: 'post',
		date: 'November 22, 2025',
	};

	test( 'returns null when result is not provided', () => {
		const { container } = render( <ResultCard /> );
		expect( container.firstChild ).toBeNull();
	} );

	test( 'returns null when result is null', () => {
		const { container } = render( <ResultCard result={ null } /> );
		expect( container.firstChild ).toBeNull();
	} );

	test( 'renders result card with title', () => {
		render( <ResultCard result={ mockResult } /> );

		expect( screen.getByText( 'Test Post Title' ) ).toBeInTheDocument();
	} );

	test( 'renders result card with excerpt', () => {
		render( <ResultCard result={ mockResult } /> );

		expect(
			screen.getByText( 'This is a test excerpt with some content.' )
		).toBeInTheDocument();
	} );

	test( 'renders result card with date', () => {
		render( <ResultCard result={ mockResult } /> );

		expect( screen.getByText( 'November 22, 2025' ) ).toBeInTheDocument();
	} );

	test( 'renders result card with type', () => {
		render( <ResultCard result={ mockResult } /> );

		expect( screen.getByText( 'post' ) ).toBeInTheDocument();
	} );

	test( 'renders thumbnail when provided', () => {
		render( <ResultCard result={ mockResult } /> );

		const thumbnail = screen.getByRole( 'img' );
		expect( thumbnail ).toHaveAttribute( 'src', mockResult.thumbnail );
		expect( thumbnail ).toHaveAttribute( 'alt', mockResult.title );
	} );

	test( 'does not render thumbnail when not provided', () => {
		const resultWithoutThumbnail = { ...mockResult, thumbnail: null };
		render( <ResultCard result={ resultWithoutThumbnail } /> );

		expect( screen.queryByRole( 'img' ) ).not.toBeInTheDocument();
	} );

	test( 'renders link with correct URL', () => {
		render( <ResultCard result={ mockResult } /> );

		const links = screen.getAllByRole( 'link' );
		links.forEach( ( link ) => {
			expect( link ).toHaveAttribute( 'href', mockResult.url );
		} );
	} );

	test( 'renders with correct data-id attribute', () => {
		const { container } = render( <ResultCard result={ mockResult } /> );

		const resultDiv = container.querySelector( '[data-id="123"]' );
		expect( resultDiv ).toBeInTheDocument();
	} );

	test( 'renders without excerpt when not provided', () => {
		const resultWithoutExcerpt = { ...mockResult, excerpt: null };
		render( <ResultCard result={ resultWithoutExcerpt } /> );

		expect(
			screen.queryByText( 'This is a test excerpt with some content.' )
		).not.toBeInTheDocument();
	} );
} );
