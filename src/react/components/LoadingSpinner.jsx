/**
 * LoadingSpinner Component
 *
 * Simple loading indicator shown while fetching results
 */

import { __ } from '@wordpress/i18n';
import './LoadingSpinner.css';

const LoadingSpinner = () => (
  <div className="searchwiz-loading">
    <div className="spinner"></div>
    <p>{__('Searching...', 'searchwiz')}</p>
  </div>
);

export default LoadingSpinner;