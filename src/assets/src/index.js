/**
 * SearchWiz React Component - WordPress Native Build
 * Save as: assets/build/index.js
 */

(() => {
    const { createElement: e, useState, useEffect } = wp.element;

    // Main SearchWiz React Component
    const SearchWizApp = ({ container, config }) => {
        const [query, setQuery] = useState('');
        const [results, setResults] = useState([]);
        const [isLoading, setIsLoading] = useState(false);
        const [error, setError] = useState(null);
        const [totalResults, setTotalResults] = useState(0);

        // Get attributes from container
        const theme = container.dataset.theme || 'modern';
        const placeholder = container.dataset.placeholder || 'Search...';
        const showFilters = container.dataset.showFilters === 'true';
        const showCount = container.dataset.showCount === 'true';

        // Search function
        const performSearch = async (searchQuery) => {
            if (!searchQuery.trim()) {
                setResults([]);
                setTotalResults(0);
                return;
            }

            setIsLoading(true);
            setError(null);

            try {
                const response = await wp.apiFetch({
                    path: 'searchwiz/v1/search',
                    method: 'POST',
                    data: {
                        query: searchQuery,
                        filters: {},
                        page: 1
                    }
                });

                setResults(response.results || []);
                setTotalResults(response.total_results || 0);
            } catch (err) {
                console.error('SearchWiz search error:', err);
                setError(err.message);
                setResults([]);
            } finally {
                setIsLoading(false);
            }
        };

        // Debounced search effect
        useEffect(() => {
            const timer = setTimeout(() => {
                performSearch(query);
            }, 300);

            return () => clearTimeout(timer);
        }, [query]);

        return e('div', { className: `searchwiz-app searchwiz-theme-${theme}` },
            // Search Form
            e('form', {
                onSubmit: (e) => {
                    e.preventDefault();
                    performSearch(query);
                },
                className: 'searchwiz-search-form'
            },
                e('div', { className: 'searchwiz-input-group' },
                    e('input', {
                        type: 'text',
                        value: query,
                        onChange: (e) => setQuery(e.target.value),
                        placeholder: placeholder,
                        className: 'searchwiz-search-input',
                        disabled: isLoading
                    }),
                    e('button', {
                        type: 'submit',
                        className: 'searchwiz-search-button',
                        disabled: isLoading || !query.trim()
                    }, isLoading ? '⏳' : '🔍')
                )
            ),

            // Results Count
            showCount && totalResults > 0 && e('div', { className: 'searchwiz-results-count' },
                `Found ${totalResults} result${totalResults !== 1 ? 's' : ''} for "${query}"`
            ),

            // Error Display
            error && e('div', { className: 'searchwiz-error' },
                `⚠️ Search error: ${error}`
            ),

            // Loading Indicator
            isLoading && e('div', { className: 'searchwiz-loading' },
                e('div', { className: 'searchwiz-spinner' }, '⏳'),
                'Searching...'
            ),

            // Search Results
            results.length > 0 && e('div', { className: 'searchwiz-results' },
                results.map(result =>
                    e('article', {
                        key: result.id,
                        className: 'searchwiz-result-item'
                    },
                        e('h3', { className: 'searchwiz-result-title' },
                            e('a', {
                                href: result.url,
                                target: '_blank',
                                rel: 'noopener noreferrer'
                            }, result.title)
                        ),
                        result.excerpt && e('p', { className: 'searchwiz-result-excerpt' },
                            result.excerpt
                        ),
                        e('div', { className: 'searchwiz-result-meta' },
                            e('span', { className: 'searchwiz-result-type' }, result.type),
                            result.author && e('span', { className: 'searchwiz-result-author' },
                                `by ${result.author}`
                            ),
                            e('span', { className: 'searchwiz-result-date' },
                                new Date(result.date).toLocaleDateString()
                            )
                        )
                    )
                )
            ),

            // No Results
            query && !isLoading && results.length === 0 && !error && e('div', { className: 'searchwiz-no-results' },
                `No results found for "${query}". Try different keywords.`
            )
        );
    };

    // Initialize SearchWiz when DOM is ready
    const initializeSearchWiz = () => {
        // Check WordPress React availability
        if (typeof wp === 'undefined' || typeof wp.element === 'undefined') {
            console.error('SearchWiz: WordPress React (wp.element) not loaded');
            return;
        }

        // Get configuration from localized script (set via wp_localize_script)
        // Do not hardcode URLs - they must come from WordPress
        const config = window.searchwizConfig || {};
        if (!config.ajaxUrl) {
            console.error('SearchWiz: ajaxUrl not configured. Please ensure wp_localize_script is properly set up.');
            return;
        }

        // Find all SearchWiz containers
        const containers = document.querySelectorAll('.searchwiz-search-container');
        console.log(`SearchWiz: Found ${containers.length} container(s)`);

        containers.forEach((container, index) => {
            try {
                // Remove loading placeholder
                const placeholder = container.querySelector('.searchwiz-loading-placeholder');
                if (placeholder) {
                    placeholder.remove();
                }

                // Render React component
                wp.element.render(
                    e(SearchWizApp, { container, config }),
                    container
                );

                console.log(`SearchWiz: Initialized container ${index + 1}`);
            } catch (error) {
                console.error(`SearchWiz: Failed to initialize container ${index + 1}:`, error);
                container.innerHTML = `
                <div class="searchwiz-init-error" style="padding: 16px; background: #ffebee; border: 1px solid #f44336; border-radius: 4px; color: #c62828;">
                    <strong>SearchWiz Error:</strong> Failed to initialize React component.
                    <br><small>${error.message}</small>
                </div>
            `;
            }
        });
    };

    // Auto-initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeSearchWiz);
    } else {
        initializeSearchWiz();
    }

    // Expose to global scope for manual initialization
    window.SearchWizApp = SearchWizApp;
    window.initializeSearchWiz = initializeSearchWiz;
})();