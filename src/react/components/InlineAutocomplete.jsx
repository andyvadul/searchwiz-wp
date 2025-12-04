/**
 * InlineAutocomplete Component
 *
 * Provides Google-style inline autocomplete suggestions.
 * Shows gray completion text inside the search input that user can accept with Tab/Right Arrow.
 */

import { useState, useEffect, useRef } from '@wordpress/element';

const InlineAutocomplete = () => {
  const [suggestion, setSuggestion] = useState('');
  const [currentInput, setCurrentInput] = useState(null);
  const [currentQuery, setCurrentQuery] = useState('');
  const debounceTimer = useRef(null);

  // Get config from PHP via wp_localize_script
  // IMPORTANT: ajax_url is dynamically set by PHP using admin_url('admin-ajax.php')
  // Never hardcode '/wp-admin/admin-ajax.php' as it may differ in some WordPress configurations
  const config = window.searchwiz || {
    ajax_url: '',
    nonce: '',
  };

  // Fetch suggestion from backend
  const fetchSuggestion = async (query, inputElement) => {
    if (query.length < 2) {
      setSuggestion('');
      return;
    }

    try {
      const formData = new FormData();
      formData.append('action', 'searchwiz_inline_suggestion');
      formData.append('security', config.nonce);
      formData.append('q', query);

      const response = await fetch(config.ajax_url, {
        method: 'POST',
        body: formData,
      });

      const data = await response.json();

      if (data.success && data.data.suggestion) {
        const suggestedTerm = data.data.suggestion;
        // Only show if it starts with the query (prefix match)
        if (suggestedTerm.toLowerCase().startsWith(query.toLowerCase())) {
          setSuggestion(suggestedTerm);
          setCurrentInput(inputElement);
          setCurrentQuery(query);
        } else {
          setSuggestion('');
          setCurrentQuery('');
        }
      } else {
        setSuggestion('');
        setCurrentQuery('');
      }
    } catch (error) {
      window.searchwizDebug?.log('❌ Autocomplete error:', error);
      setSuggestion('');
    }
  };

  // Handle keyboard events
  useEffect(() => {
    const searchInputs = document.querySelectorAll('.searchwiz-search-input');

    if (searchInputs.length === 0) {
      return;
    }

    const handleInput = (e) => {
      const query = e.target.value.trim();

      // IMMEDIATELY remove overlay from DOM when user types
      const existingOverlay = document.querySelector('.searchwiz-inline-suggestion');
      if (existingOverlay) {
        existingOverlay.remove();
      }

      // Clear existing timer
      if (debounceTimer.current) {
        clearTimeout(debounceTimer.current);
      }

      // If query is empty or too short, clear suggestion immediately
      if (query.length < 2) {
        setSuggestion('');
        setCurrentQuery('');
        return;
      }

      // Clear current suggestion state
      setSuggestion('');
      setCurrentQuery('');

      // Debounce suggestion fetch (300ms delay - longer to ensure visible clearing)
      debounceTimer.current = setTimeout(() => {
        fetchSuggestion(query, e.target);
      }, 300);
    };

    const handleKeyDown = (e) => {
      const input = e.target;
      const query = input.value;

      // Tab or Right Arrow: Accept suggestion
      if ((e.key === 'Tab' || e.key === 'ArrowRight') && suggestion) {
        // Only accept if cursor is at end of input
        if (input.selectionStart === query.length) {
          e.preventDefault();
          input.value = suggestion;
          setSuggestion('');

          // Trigger input event to update search results
          input.dispatchEvent(new Event('input', { bubbles: true }));
        }
      }

      // Escape: Clear suggestion
      if (e.key === 'Escape' && suggestion) {
        setSuggestion('');
      }

      // Any other key that changes input: Will trigger handleInput
    };

    // Attach listeners to all search inputs
    searchInputs.forEach((input) => {
      input.addEventListener('input', handleInput);
      input.addEventListener('keydown', handleKeyDown);
    });

    // Cleanup
    return () => {
      if (debounceTimer.current) {
        clearTimeout(debounceTimer.current);
      }
      searchInputs.forEach((input) => {
        input.removeEventListener('input', handleInput);
        input.removeEventListener('keydown', handleKeyDown);
      });
    };
  }, [suggestion]);

  // Render inline suggestion overlay
  useEffect(() => {
    // ALWAYS remove any existing overlay first - fresh start every time
    const existingOverlay = document.querySelector('.searchwiz-inline-suggestion');
    if (existingOverlay) {
      existingOverlay.remove();
    }

    // Early exit conditions - don't create overlay if any of these are true
    if (!currentInput || !suggestion || !currentQuery) {
      return;
    }

    // Calculate what part of the suggestion to show
    const currentValue = currentInput.value.trim();
    const completionPart = suggestion.substring(currentQuery.length);

    // Don't show if:
    // - No completion to show
    // - Current input doesn't match the query we fetched for
    if (!completionPart || currentValue.toLowerCase() !== currentQuery.toLowerCase()) {
      return;
    }

    // Create fresh overlay
    const overlay = document.createElement('div');
    overlay.className = 'searchwiz-inline-suggestion';
    currentInput.parentElement.style.position = 'relative';

    const inputStyle = window.getComputedStyle(currentInput);

    // Make overlay an exact copy of the input styling
    overlay.style.position = 'absolute';
    overlay.style.top = currentInput.offsetTop + 'px';
    overlay.style.left = currentInput.offsetLeft + 'px';
    overlay.style.width = currentInput.offsetWidth + 'px';
    overlay.style.height = currentInput.offsetHeight + 'px';
    overlay.style.pointerEvents = 'none';
    overlay.style.overflow = 'hidden';
    overlay.style.whiteSpace = 'pre';
    overlay.style.zIndex = '1';

    // Use block display to behave like the input
    overlay.style.display = 'block';

    // Copy ALL text and box styles from input for perfect pixel alignment
    overlay.style.fontFamily = inputStyle.fontFamily;
    overlay.style.fontSize = inputStyle.fontSize;
    overlay.style.fontWeight = inputStyle.fontWeight;
    overlay.style.letterSpacing = inputStyle.letterSpacing;
    overlay.style.textTransform = inputStyle.textTransform;
    overlay.style.paddingTop = inputStyle.paddingTop;
    overlay.style.paddingBottom = inputStyle.paddingBottom;
    overlay.style.paddingLeft = inputStyle.paddingLeft;
    overlay.style.paddingRight = inputStyle.paddingRight;
    overlay.style.margin = '0';
    overlay.style.border = 'none';
    overlay.style.boxSizing = inputStyle.boxSizing;
    overlay.style.textAlign = inputStyle.textAlign;
    overlay.style.lineHeight = inputStyle.lineHeight;
    overlay.style.verticalAlign = inputStyle.verticalAlign;

    // Match exact rendering mode
    overlay.style.fontSmoothing = inputStyle.fontSmoothing;
    overlay.style.webkitFontSmoothing = inputStyle.webkitFontSmoothing;
    overlay.style.mozOsxFontSmoothing = inputStyle.mozOsxFontSmoothing;

    // Content: Simple inline spans with no additional styling
    overlay.innerHTML = `<span style="visibility: hidden;">${currentQuery}</span><span style="color: #999;">${completionPart}</span>`;

    // Append the overlay to the DOM
    currentInput.parentElement.appendChild(overlay);

    // Cleanup
    return () => {
      const existingOverlay = document.querySelector('.searchwiz-inline-suggestion');
      if (existingOverlay) {
        existingOverlay.remove();
      }
    };
  }, [suggestion, currentInput, currentQuery]);

  // This component doesn't render anything visible in React
  // It manipulates the DOM directly for better performance
  return null;
};

export default InlineAutocomplete;
