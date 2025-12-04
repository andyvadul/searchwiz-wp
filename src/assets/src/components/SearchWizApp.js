// assets/src/components/SearchWizApp.js
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

const SearchWizApp = () => {
    const [results, setResults] = useState([]);
    const [loading, setLoading] = useState(false);
    
    const handleSearch = async (query) => {
        setLoading(true);
        try {
            const data = await apiFetch({
                path: `/searchwiz/v1/search?q=${query}`
            });
            setResults(data);
        } catch (error) {
            console.error('Search error:', error);
        } finally {
            setLoading(false);
        }
    };
    
    return (
        <div className="searchwiz-app">
            {/* Your search interface */}
        </div>
    );
};

export default SearchWizApp;