/**
 * SearchWiz React Frontend Entry Point
 * 
 * This file is the webpack entry point for building React components.
 * Uses WordPress core React (wp.element) instead of standalone React.
 * 
 * Build command: npm run build
 * Output: public/dist/index.js (and style bundle)
 */

import { createRoot } from '@wordpress/element';
import SearchResults from './components/SearchResults';
import InlineAutocomplete from './components/InlineAutocomplete';
import './styles/app.css';

/**
 * Initialize React component when DOM is ready
 */
function mountReactApp() {
  // Find the mount point (will be added by PHP loader)
  const container = document.getElementById('searchwiz-react-results');

  if (container) {
    const root = createRoot(container);
    root.render(
      <>
        <SearchResults />
        <InlineAutocomplete />
      </>
    );
  }
}

// Handle both early and late script loading
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', mountReactApp);
} else {
  mountReactApp();
}