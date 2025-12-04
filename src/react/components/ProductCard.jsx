/**
 * ProductCard Component
 *
 * Renders a WooCommerce product with price, stock status, and ratings.
 *
 * Note: Search term highlighting is done server-side via <mark> tags
 */

import { __ } from '@wordpress/i18n';

const ProductCard = ({ result }) => {
  if (!result || !result.product) return null;

  const { id, title, excerpt, url, thumbnail, product } = result;

  /**
   * Create safe HTML object for dangerouslySetInnerHTML
   * Backend adds <mark> tags around search terms
   */
  const createMarkup = (html) => {
    return { __html: html || '' };
  };

  return (
    <div data-id={id} className={`searchwiz-product-card`} style={{
      display: 'block',
      padding: '15px',
      border: '1px solid #ddd',
      borderRadius: '4px',
      marginBottom: '10px',
      background: 'white',
      height: 'auto',
      overflow: 'visible'
    }}>
      <div style={{ display: 'flex', gap: '15px' }}>
        {/* Product Image */}
        {thumbnail && (
          <div style={{ flexShrink: 0 }}>
            <a href={url}>
              <img
                src={thumbnail}
                alt={title}
                style={{
                  width: '100px',
                  height: '100px',
                  objectFit: 'cover',
                  borderRadius: '4px'
                }}
              />
            </a>
          </div>
        )}

        {/* Product Info */}
        <div style={{ flex: 1, minWidth: 0 }}>
          {/* Title */}
          <div style={{ marginBottom: '8px' }}>
            <a href={url} style={{
              color: '#0073aa',
              fontSize: '16px',
              fontWeight: 'bold',
              textDecoration: 'none',
              display: 'block'
            }} dangerouslySetInnerHTML={createMarkup(title)} />
          </div>

          {/* Price and Stock */}
          <div style={{
            display: 'flex',
            alignItems: 'center',
            gap: '15px',
            marginBottom: '8px'
          }}>
            {/* Price */}
            {product.price_html && (
              <div
                style={{ fontSize: '18px', fontWeight: 'bold', color: '#333' }}
                dangerouslySetInnerHTML={{ __html: product.price_html }}
              />
            )}

            {/* Stock Status */}
            {product.in_stock !== undefined && (
              <div style={{
                padding: '3px 8px',
                borderRadius: '3px',
                fontSize: '12px',
                fontWeight: '600',
                background: product.in_stock ? '#d4edda' : '#f8d7da',
                color: product.in_stock ? '#155724' : '#721c24'
              }}>
                {product.in_stock ? __('In Stock', 'searchwiz') : __('Out of Stock', 'searchwiz')}
              </div>
            )}

            {/* Sale Badge */}
            {product.on_sale && (
              <div style={{
                padding: '3px 8px',
                borderRadius: '3px',
                fontSize: '12px',
                fontWeight: '600',
                background: '#ff6b6b',
                color: 'white'
              }}>
                {__('SALE!', 'searchwiz')}
              </div>
            )}
          </div>

          {/* Rating */}
          {product.rating > 0 && (
            <div style={{ marginBottom: '8px', fontSize: '13px', color: '#666' }}>
              ★ {product.rating} ({product.review_count} {__('reviews', 'searchwiz')})
            </div>
          )}

          {/* Excerpt */}
          {excerpt && (
            <div style={{ fontSize: '14px', lineHeight: '1.5', color: '#555' }} dangerouslySetInnerHTML={createMarkup(excerpt)} />
          )}

          {/* SKU */}
          {product.sku && (
            <div style={{ fontSize: '12px', color: '#999', marginTop: '5px' }}>
              {__('SKU:', 'searchwiz')} {product.sku}
            </div>
          )}
        </div>
      </div>
    </div>
  );
};

export default ProductCard;
