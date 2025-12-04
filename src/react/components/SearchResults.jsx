/**
 * SearchResults Component
 *
 * Main React component that displays search results.
 * Listens to search input changes and fetches results via AJAX.
 */

import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import ResultCard from './ResultCard';
import ProductCard from './ProductCard';
import LoadingSpinner from './LoadingSpinner';

const SearchResults = () => {
  const [results, setResults] = useState([]);
  const [products, setProducts] = useState([]); // WooCommerce products
  const [loading, setLoading] = useState(false);
  const [loadingMore, setLoadingMore] = useState(false); // Loading more results
  const [error, setError] = useState(null);
  const [currentQuery, setCurrentQuery] = useState('');
  const [activeInput, setActiveInput] = useState(null); // Track which search box is active
  const [woocommerceActive, setWoocommerceActive] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const [hasMore, setHasMore] = useState(false); // TRUE PAGINATION: Is there a next page?
  const [debounceTimer, setDebounceTimer] = useState(null);
  const [isMaximized, setIsMaximized] = useState(false); // Track maximize/minimize state
  const [columnWidth, setColumnWidth] = useState(65); // Percentage width for products column (left)
  const [isDragging, setIsDragging] = useState(false); // Track if user is dragging the divider
  const isDebugMode = window.searchwizDebug?.enabled || false; // Check if debug mode is enabled

  // Track if we STARTED on a search results page (persists even if URL changes)
  // Check for isSearchpage=true parameter OR old behavior (s= parameter)
  const [isSearchPage] = useState(() => {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('isSearchpage') === 'true' || urlParams.has('s');
  });

  // Get config from PHP via wp_localize_script
  // IMPORTANT: ajax_url is dynamically set by PHP using admin_url('admin-ajax.php')
  // Never hardcode '/wp-admin/admin-ajax.php' as it may differ in some WordPress configurations
  const config = window.searchwiz || {
    ajax_url: '',
    nonce: '',
    query: '',
  };

  // Show/hide and position results container next to the ACTIVE search input
  useEffect(() => {
    const container = document.getElementById('searchwiz-react-results');
    if (container) {
      if (loading || results.length > 0 || error) {
        container.style.display = 'block';

        // On search results page, don't reposition - PHP already positioned it
        if (isSearchPage) {
          // Keep the container visible, PHP has already positioned it
          window.searchwizDebug?.log('📄 Search results page - using PHP positioning');
          return;
        }

        // Reposition container near the ACTIVE search input (not just the first one)
        if (activeInput) {
          const searchForm = activeInput.closest('form') || activeInput.parentElement;
          if (searchForm) {
            // Move container to be sibling of search form if not already
            if (container.parentElement !== searchForm.parentElement) {
              searchForm.parentElement.insertBefore(container, searchForm.nextSibling);
            }

            // Position container directly below search form
            const formRect = searchForm.getBoundingClientRect();
            container.style.overflowY = 'visible'; // No scrolling - container grows naturally

            // Add smooth transition animation - only animate width and left (not position)
            container.style.transition = 'width 0.3s ease-in-out, left 0.3s ease-in-out, min-width 0.3s ease-in-out, max-width 0.3s ease-in-out';

            // Handle maximize/minimize states
            if (isMaximized) {
              // Maximized: Use FIXED positioning to center on screen
              const viewportWidth = window.innerWidth;
              const maxWidth = viewportWidth * 0.8;

              window.searchwizDebug?.log('🔼 MAXIMIZING', { viewportWidth, maxWidth, formRect });

              container.style.position = 'fixed';
              container.style.width = `${maxWidth}px`;
              container.style.minWidth = `${maxWidth}px`;
              container.style.maxWidth = `${maxWidth}px`;
              container.style.left = `${(viewportWidth - maxWidth) / 2}px`; // Center horizontally
              container.style.top = `${formRect.bottom + 10}px`; // Below search form
              container.style.zIndex = '1000000';
            } else {
              // Normal: Absolute position below search form, aligned with form
              const normalWidth = Math.max(formRect.width, 600);

              // Calculate left offset to align with search form when width is larger
              const leftOffset = normalWidth > formRect.width ? -((normalWidth - formRect.width) / 2) : 0;

              window.searchwizDebug?.log('🔽 MINIMIZING', { normalWidth, formRect, leftOffset });

              container.style.position = 'absolute';
              container.style.top = `${formRect.height}px`;
              container.style.left = `${leftOffset}px`;
              container.style.width = `${normalWidth}px`;
              container.style.minWidth = `${normalWidth}px`;
              container.style.maxWidth = `${normalWidth}px`;
              container.style.zIndex = '999999';
            }
          }
        }
      } else if (currentQuery.length === 0) {
        container.style.display = 'none';
      }
    }
  }, [results, loading, error, currentQuery, activeInput, isMaximized]);

  useEffect(() => {
    // Find ALL SearchWiz search inputs
    const searchInputs = document.querySelectorAll('.searchwiz-search-input');

    let lastValue = '';
    let searchTimeout = null;

    const processQuery = (query, inputElement) => {
      setCurrentQuery(query);
      setActiveInput(inputElement); // Update state so positioning effect can use it

      // Clear any existing search timeout
      if (searchTimeout) {
        clearTimeout(searchTimeout);
        searchTimeout = null;
      }

      // If query is empty or less than 3 characters, clear results immediately
      if (query.length < 3) {
        setResults([]);
        setProducts([]);
        setError(null);
        setCurrentPage(1);
        return;
      }

      // Add debounce delay (250ms) before searching
      searchTimeout = setTimeout(() => {
        setCurrentPage(1);
        setResults([]);
        setProducts([]);
        fetchResults(query, 1);
      }, 250);
    };

    // Check if we're on a search results page with a query parameter
    const urlParams = new URLSearchParams(window.location.search);
    const urlQuery = urlParams.get('s');
    if (urlQuery && urlQuery.trim().length >= 3) {
      // Auto-load results from URL on search results page
      // BUT: Check if we already have results for this query (from inline search)
      // If we do, reuse them instead of fetching again!
      if (currentQuery !== urlQuery.trim()) {
        // Only fetch if the query is different from what we already have
        processQuery(urlQuery.trim(), null);
      } else {
        // We already have results for this query - just update activeInput
        setActiveInput(null);
      }
    }

    // If no search inputs found and not on search results page, return early
    if (searchInputs.length === 0 && !urlQuery) {
      window.searchwizDebug?.log('⚠️ Search input (.searchwiz-search-input) not found and not on search results page');
      return;
    }

    // Polling approach - check ALL inputs every 300ms
    // This works even if events are blocked by other scripts
    const pollInterval = setInterval(() => {
      let foundNonEmpty = false;
      for (let input of searchInputs) {
        const currentValue = input.value.trim();
        if (currentValue) {
          foundNonEmpty = true;
          if (currentValue !== lastValue) {
            lastValue = currentValue;
            processQuery(currentValue, input);
          }
          break;
        }
      }

      // If no inputs have values but we had a value before, clear it
      if (!foundNonEmpty && lastValue) {
        lastValue = '';
        processQuery('', null);
      }
    }, 300);

    // Also try event listeners on ALL inputs as backup
    const handleInput = (e) => {
      const query = e.target.value.trim();
      if (query !== lastValue) {
        lastValue = query;
        processQuery(query, e.target);
      }
    };

    searchInputs.forEach((input) => {
      input.addEventListener('input', handleInput, true);
      input.addEventListener('keyup', handleInput, true);
    });

    // Cleanup
    return () => {
      clearInterval(pollInterval);
      if (searchTimeout) {
        clearTimeout(searchTimeout);
      }
      searchInputs.forEach(input => {
        input.removeEventListener('input', handleInput, true);
        input.removeEventListener('keyup', handleInput, true);
      });
    };
  }, []);

  /**
   * Fetch search results from new JSON AJAX handler
   * @param {string} query - Search query
   * @param {number} page - Page number to fetch
   * @param {boolean} append - Whether to append results or replace them
   */
  const fetchResults = async (query, page = 1, append = false) => {
    if (append) {
      setLoadingMore(true);
    } else {
      setLoading(true);
    }
    setError(null);

    try {
      window.searchwizDebug?.log(`📄 Fetching page ${page} for "${query}"`, { append });

      const formData = new FormData();
      formData.append('action', 'searchwiz_search_json');
      formData.append('s', query);
      formData.append('id', config.form_id || 8);
      formData.append('page', page);
      formData.append('security', config.nonce);

      const response = await fetch(config.ajax_url, {
        method: 'POST',
        credentials: 'same-origin',
        body: formData,
      });

      const data = await response.json();

      if (data.success) {
        window.searchwizDebug?.log(`✅ Page ${page} loaded:`, {
          products: data.data.products?.length || 0,
          posts: data.data.results?.length || 0,
          hasMore: data.data.has_more
        });

        // Update pagination info (TRUE PAGINATION)
        setHasMore(data.data.has_more || false);
        setWoocommerceActive(data.data.woocommerce_active || false);

        // Append or replace results
        if (append) {
          setResults(prev => [...prev, ...(data.data.results || [])]);
          setProducts(prev => [...prev, ...(data.data.products || [])]);
        } else {
          setResults(data.data.results || []);
          setProducts(data.data.products || []);
        }
      } else {
        if (!append) {
          setResults([]);
          setProducts([]);
        }
      }
    } catch (err) {
      setError(__('Failed to fetch results', 'searchwiz'));
    } finally {
      setLoading(false);
      setLoadingMore(false);
    }
  };

  /**
   * Load more results when user scrolls to bottom
   */
  const loadMore = () => {
    if (!loadingMore && hasMore && currentQuery) {
      setLoadingMore(true);
      const nextPage = currentPage + 1;
      setCurrentPage(nextPage);
      fetchResults(currentQuery, nextPage, true);
    }
  };

  /**
   * Infinite scroll - IntersectionObserver (Industry Standard 2025)
   * Observes sentinel element at bottom of list, loads more when it becomes visible
   */
  useEffect(() => {
    if (!hasMore || loadingMore) return;

    // Wait for sentinel to appear in DOM after results render
    const sentinel = document.getElementById('searchwiz-scroll-sentinel');
    if (!sentinel) {
      window.searchwizDebug?.log('⚠️ Sentinel not found, waiting for next render');
      return;
    }

    // Industry-standard configuration
    const observer = new IntersectionObserver(
      (entries) => {
        const entry = entries[0];
        if (entry.isIntersecting) {
          window.searchwizDebug?.log(`🔽 Sentinel visible - loading page ${currentPage + 1}`);
          loadMore();
        }
      },
      {
        root: null,           // Use viewport (not container)
        threshold: 0.1,       // Trigger when 10% visible
        rootMargin: '100px'   // Load 100px BEFORE sentinel is visible (smooth UX)
      }
    );

    window.searchwizDebug?.log('👁️ Observing sentinel', { hasMore, currentPage });
    observer.observe(sentinel);
    return () => observer.disconnect();
  }, [hasMore, loadingMore, currentPage, results, products]);

  const totalResults = products.length + results.length;
  const hasProducts = products.length > 0;
  const hasContent = results.length > 0;

  /**
   * Close/dismiss the search results
   */
  const closeResults = () => {
    try {
      window.searchwizDebug?.log('❌ closeResults called', {
        resultsCount: results.length,
        currentQuery: currentQuery,
        stackTrace: new Error().stack
      });
      setResults([]);
      setProducts([]);
      setCurrentQuery('');
      setError(null);
      setCurrentPage(1);

      // Also clear the search input
      if (activeInput) {
        activeInput.value = '';
      }
    } catch (error) {
      window.searchwizDebug?.log('❌ Error in closeResults:', error);
      console.error('Error closing results:', error);
    }
  };

  /**
   * Handle ESC key to close results - GLOBAL listener
   */
  useEffect(() => {
    const handleEscKey = (e) => {
      if (e.key === 'Escape' && (results.length > 0 || products.length > 0 || error)) {
        e.preventDefault();
        e.stopPropagation();
        closeResults();
      }
    };

    // Add global keydown listener
    document.addEventListener('keydown', handleEscKey, true); // Use capture phase

    return () => {
      document.removeEventListener('keydown', handleEscKey, true);
    };
  }, [results, products, error, activeInput]);

  /**
   * Handle search input change on search results page
   * Updates URL without page refresh using history.pushState
   */
  const handleSearchPageInput = (e) => {
    const newQuery = e.target.value;

    // Clear any existing debounce timer
    if (debounceTimer) {
      clearTimeout(debounceTimer);
    }

    if (newQuery.length >= 3) {
      // Debounce search by 300ms
      const timer = setTimeout(() => {
        // Update URL without page refresh, keeping isSearchpage=true
        const newUrl = new URL(window.location);
        newUrl.searchParams.set('isSearchpage', 'true');
        newUrl.searchParams.set('s', newQuery);
        window.history.pushState({}, '', newUrl);

        // Trigger new search
        setCurrentQuery(newQuery);
        setCurrentPage(1);
        setResults([]);
        setProducts([]);
        fetchResults(newQuery, 1);
      }, 300);

      setDebounceTimer(timer);
    } else if (newQuery.length === 0) {
      // Clear results if query is empty
      setCurrentQuery('');
      setResults([]);
      setProducts([]);

      // Update URL to remove search param BUT KEEP isSearchpage=true
      const newUrl = new URL(window.location);
      newUrl.searchParams.set('isSearchpage', 'true');
      newUrl.searchParams.delete('s');
      window.history.pushState({}, '', newUrl);
    }
  };

  /**
   * Handle column divider drag for resizable columns
   */
  const handleDividerMouseDown = (e) => {
    e.preventDefault();
    setIsDragging(true);
  };

  /**
   * Handle mouse move during drag
   */
  const handleMouseMove = (e) => {
    if (!isDragging) return;

    const container = e.currentTarget.closest('[data-resizable-container]');
    if (!container) return;

    const containerRect = container.getBoundingClientRect();
    const mouseX = e.clientX - containerRect.left;
    const newWidth = (mouseX / containerRect.width) * 100;

    // Limit column width between 30% and 70%
    if (newWidth >= 30 && newWidth <= 70) {
      setColumnWidth(newWidth);
    }
  };

  /**
   * Handle mouse up - stop dragging
   */
  const handleMouseUp = () => {
    setIsDragging(false);
  };

  // Use two-column layout if WooCommerce is active and we have products
  return (
    <div style={{
      // Position and width set dynamically by useEffect above
      background: isSearchPage ? 'transparent' : '#f0f0f0',
      border: isSearchPage ? 'none' : '2px solid #0073aa',
      padding: '20px',
      zIndex: 999999,
      boxShadow: isSearchPage ? 'none' : '0 2px 10px rgba(0,0,0,0.1)',
      display: 'block',
      minHeight: '50px',
      maxHeight: isSearchPage ? 'none' : '80vh',
      overflowY: isSearchPage ? 'visible' : 'auto'
    }}>
      {/* Maximize/Minimize Button - Only show on inline search, not on search results page */}
      {!isSearchPage && (
        <button
          onClick={(e) => {
            e.preventDefault();
            e.stopPropagation();
            const newState = !isMaximized;
            window.searchwizDebug?.log(`🔲 Maximize button clicked: ${isMaximized ? 'MINIMIZING' : 'MAXIMIZING'} -> ${newState}`, {
              currentState: isMaximized,
              newState: newState,
              currentQuery: currentQuery,
              resultsCount: results.length
            });
            setIsMaximized(newState);
          }}
        style={{
          position: 'absolute',
          top: '10px',
          right: '55px',
          background: isDebugMode ? '#f0f0f0' : 'transparent',
          color: '#0073aa',
          border: 'none',
          outline: 'none',
          borderRadius: isDebugMode ? '3px' : '0',
          width: '32px',
          height: '32px',
          cursor: 'pointer',
          fontSize: '18px',
          fontWeight: 'bold',
          lineHeight: '1',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          zIndex: 1000000,
          transition: 'transform 0.2s'
        }}
        onMouseOver={(e) => {
          e.target.style.transform = 'scale(1.1)';
          if (isDebugMode) {
            e.target.style.background = '#e8e8e8';
          }
        }}
        onMouseOut={(e) => {
          e.target.style.transform = 'scale(1)';
          if (isDebugMode) {
            e.target.style.background = '#f0f0f0';
          }
        }}
        title={isMaximized ? __('Minimize', 'searchwiz') : __('Maximize', 'searchwiz')}
      >
        {isMaximized ? '▼' : '▲'}
      </button>
      )}

      {/* Close Button - Only show on inline search, not on search results page */}
      {!isSearchPage && (
        <button
          onClick={(e) => {
            e.preventDefault();
            e.stopPropagation();
            window.searchwizDebug?.log('❌ Close button (×) clicked');
            closeResults();
          }}
        style={{
          position: 'absolute',
          top: '10px',
          right: '10px',
          background: isDebugMode ? '#ffebee' : 'transparent',
          color: '#d32f2f',
          border: 'none',
          outline: 'none',
          borderRadius: isDebugMode ? '3px' : '0',
          width: '32px',
          height: '32px',
          cursor: 'pointer',
          fontSize: '20px',
          fontWeight: 'bold',
          lineHeight: '1',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          zIndex: 1000000,
          transition: 'transform 0.2s'
        }}
        onMouseOver={(e) => {
          e.target.style.transform = 'scale(1.1)';
          if (isDebugMode) {
            e.target.style.background = '#ffcdd2';
          }
        }}
        onMouseOut={(e) => {
          e.target.style.transform = 'scale(1)';
          if (isDebugMode) {
            e.target.style.background = '#ffebee';
          }
        }}
        title={__('Close search results', 'searchwiz')}
      >
        ×
      </button>
      )}

      {/* Header */}
      <h3 style={{
        margin: '0 0 15px 0',
        color: '#333',
        fontSize: '18px',
        borderBottom: '2px solid #0073aa',
        paddingBottom: '10px',
        paddingRight: isSearchPage ? '0' : '80px' // No padding needed on search page (no buttons)
      }}>
        {__('Search Results', 'searchwiz')}
        {totalResults > 0 && (
          <span style={{ fontSize: '14px', color: '#666', fontWeight: 'normal', marginLeft: '10px' }}>
            ({totalResults}{hasMore ? '+' : ''} {__('shown', 'searchwiz')})
          </span>
        )}
        {currentQuery && (
          <span style={{ fontSize: '14px', color: '#666', fontWeight: 'normal', marginLeft: '10px' }}>
            {__('for', 'searchwiz')} "{currentQuery}"
          </span>
        )}
      </h3>

      {/* Search Box on Search Results Page */}
      {isSearchPage && (
        <div style={{ marginBottom: '20px', position: 'relative' }}>
          <input
            type="search"
            className="searchwiz-search-input"
            value={currentQuery}
            onChange={handleSearchPageInput}
            placeholder={__('Search...', 'searchwiz')}
            autoFocus={currentQuery.length === 0}
            style={{
              width: '100%',
              maxWidth: '600px',
              padding: '12px 16px',
              fontSize: '16px',
              border: '2px solid #0073aa',
              borderRadius: '4px',
              boxSizing: 'border-box',
              outline: 'none'
            }}
            onFocus={(e) => {
              e.target.style.borderColor = '#005a87';
              e.target.style.boxShadow = '0 0 0 3px rgba(0,115,170,0.1)';
            }}
            onBlur={(e) => {
              e.target.style.borderColor = '#0073aa';
              e.target.style.boxShadow = 'none';
            }}
          />
        </div>
      )}

      {/* Loading State */}
      {loading && <div style={{ padding: '10px', color: '#666' }}>{__('Loading...', 'searchwiz')}</div>}

      {/* Error State */}
      {error && <div style={{ padding: '10px', color: 'red' }}>{error}</div>}

      {/* No Results - Only show if there's an actual query (not empty) */}
      {!loading && totalResults === 0 && currentQuery && currentQuery.length >= 3 && (
        <div style={{ padding: '10px', color: '#666' }}>{__('No results found for', 'searchwiz')} "{currentQuery}"</div>
      )}

      {/* Empty Search Box Message - Show when query is cleared on search results page */}
      {isSearchPage && !loading && currentQuery.length === 0 && (
        <div style={{ padding: '20px', textAlign: 'center', color: '#666', fontSize: '15px' }}>
          {__('Enter a search term above to find products, pages, and posts', 'searchwiz')}
        </div>
      )}

      {/* Two-Column Layout: Products (Main) + Content (Secondary) with Draggable Divider */}
      {!loading && (hasProducts || hasContent) && (
        <div
          data-resizable-container
          onMouseMove={handleMouseMove}
          onMouseUp={handleMouseUp}
          onMouseLeave={handleMouseUp}
          style={{
            display: 'flex',
            gap: '0',
            position: 'relative',
            userSelect: isDragging ? 'none' : 'auto',
            cursor: isDragging ? 'col-resize' : 'default'
          }}
        >
          {/* Products Column (Main Section) */}
          {hasProducts && (
            <div style={{
              width: woocommerceActive && hasProducts && hasContent ? `${columnWidth}%` : '100%',
              paddingRight: woocommerceActive && hasProducts && hasContent ? '10px' : '0'
            }}>
              <h4 style={{ margin: '0 0 15px 0', fontSize: '16px', color: '#333', borderBottom: '1px solid #ddd', paddingBottom: '8px' }}>
                {__('Products', 'searchwiz')} ({products.length})
              </h4>
              {products.map((product) => (
                <ProductCard key={product.id} result={product} />
              ))}
            </div>
          )}

          {/* Draggable Divider - Only show when both columns are visible */}
          {woocommerceActive && hasProducts && hasContent && (
            <div
              onMouseDown={handleDividerMouseDown}
              style={{
                width: '8px',
                cursor: 'col-resize',
                background: isDragging ? '#0073aa' : '#ddd',
                position: 'relative',
                flexShrink: 0,
                transition: isDragging ? 'none' : 'background 0.2s',
                borderRadius: '4px',
                margin: '0 6px'
              }}
              onMouseEnter={(e) => {
                if (!isDragging) e.target.style.background = '#999';
              }}
              onMouseLeave={(e) => {
                if (!isDragging) e.target.style.background = '#ddd';
              }}
            >
              {/* Visual indicator */}
              <div style={{
                position: 'absolute',
                top: '50%',
                left: '50%',
                transform: 'translate(-50%, -50%)',
                width: '2px',
                height: '40px',
                background: isDragging ? 'white' : '#666',
                borderRadius: '1px'
              }} />
            </div>
          )}

          {/* Content Column (Secondary Section) - Only show if WooCommerce active and has products */}
          {hasContent && (!woocommerceActive || !hasProducts || (woocommerceActive && hasProducts)) && (
            <div style={{
              width: woocommerceActive && hasProducts && hasContent ? `${100 - columnWidth}%` : '100%',
              paddingLeft: woocommerceActive && hasProducts && hasContent ? '10px' : '0'
            }}>
              {woocommerceActive && hasProducts && (
                <h4 style={{ margin: '0 0 15px 0', fontSize: '16px', color: '#333', borderBottom: '1px solid #ddd', paddingBottom: '8px' }}>
                  {__('Pages & Posts', 'searchwiz')} ({results.length})
                </h4>
              )}
              {results.map((result) => (
                <ResultCard key={result.id} result={result} />
              ))}
            </div>
          )}
        </div>
      )}

      {/* Infinite Scroll Sentinel - IntersectionObserver target */}
      {!loading && hasMore && totalResults > 0 && (
        <div
          id="searchwiz-scroll-sentinel"
          style={{
            height: '20px',
            width: '100%',
            marginTop: '10px'
          }}
        />
      )}

      {/* Loading More Indicator */}
      {loadingMore && (
        <div style={{ padding: '20px', textAlign: 'center', color: '#666' }}>
          <div style={{ fontSize: '14px' }}>{__('Loading more results...', 'searchwiz')}</div>
        </div>
      )}

      {/* End of Results Message */}
      {!loading && !hasMore && totalResults > 0 && (
        <div style={{
          padding: '20px',
          textAlign: 'center',
          color: '#999',
          fontSize: '14px',
          borderTop: '1px solid #ddd',
          marginTop: '20px'
        }}>
          {__('All results shown', 'searchwiz')}
        </div>
      )}
    </div>
  );
};

export default SearchResults;