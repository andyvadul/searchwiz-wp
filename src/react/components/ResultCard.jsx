/**
 * ResultCard Component
 *
 * Renders a single search result with standard WordPress post data.
 * Uses existing CSS classes from searchwiz-ajax-search.css
 *
 * Note: Search term highlighting is done server-side via <mark> tags
 */

const ResultCard = ({ result }) => {
  if (!result) return null;

  const { id, title, excerpt, url, thumbnail, type, date } = result;

  /**
   * Create safe HTML object for dangerouslySetInnerHTML
   * Backend adds <mark> tags around search terms
   */
  const createMarkup = (html) => {
    return { __html: html || '' };
  };

  return (
    <div data-id={id} className={`is-ajax-search-post is-ajax-search-post-${id}`} style={{
      display: 'block',
      padding: '10px',
      borderBottom: '1px solid #ccc',
      height: 'auto',
      overflow: 'visible'
    }}>
      <div className="is-search-sections" style={{ display: 'block', height: 'auto' }}>
        {thumbnail && (
          <div className="left-section" style={{ float: 'left', marginRight: '15px' }}>
            <div className="thumbnail">
              <a href={url}>
                <img src={thumbnail} alt={title} style={{ maxWidth: '100px' }} />
              </a>
            </div>
          </div>
        )}

        <div className="right-section" style={{ display: 'block', overflow: 'hidden' }}>
          <div className="is-title" style={{ marginBottom: '8px' }}>
            <a href={url} style={{ color: '#0073aa', fontSize: '16px', fontWeight: 'bold', textDecoration: 'none' }} dangerouslySetInnerHTML={createMarkup(title)} />
          </div>

          <div className="meta" style={{ marginBottom: '8px', fontSize: '13px', color: '#666' }}>
            <div>
              {date && <span className="is-date" style={{ marginRight: '10px' }}>{date}</span>}
              {type && <span className="is-type">{type}</span>}
            </div>
          </div>

          {excerpt && (
            <div className="is-search-content" style={{ fontSize: '14px', lineHeight: '1.5' }}>
              <div className="is-description" style={{ color: '#313131' }} dangerouslySetInnerHTML={createMarkup(excerpt)} />
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default ResultCard;