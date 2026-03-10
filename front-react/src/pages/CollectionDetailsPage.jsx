import React, { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { apiFetch } from '../api/client.js';

export default function CollectionDetailsPage() {
  const { id } = useParams();
  const [collection, setCollection] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    (async () => {
      setLoading(true);
      const res = await apiFetch(`/collections/${id}`, { method: 'GET', auth: false });
      if (res.ok && res.json) {
        setCollection(res.json);
      } else {
        setError('Collection not found.');
      }
      setLoading(false);
    })();
  }, [id]);

  if (loading) return <p>Loading collection details...</p>;
  if (error) return <p className="error">{error}</p>;
  if (!collection) return null;

  return (
    <section>
      <h2>{collection.name}</h2>
      <p>{collection.description}</p>
      
      <h3>Items in this Collection</h3>
      {collection.items && collection.items.length > 0 ? (
        <div className="grid">
          {collection.items.map((item) => (
            <div key={item.id} className="card">
              <h4>
                <Link to={`/items/${item.id}`}>{item.name}</Link>
              </h4>
              {item.image && (
                <img
                  src={item.image}
                  alt={item.name}
                  style={{ maxWidth: '100%', height: 'auto', borderRadius: '4px' }}
                  onError={(e) => {
                    e.target.onerror = null;
                    e.target.src = 'https://placehold.co/600x400?text=No+Image'; // Fallback
                  }}
                />
              )}
              <p>State: {item.state}</p>
              <p>Acquisition Date: {item.acquisition_date}</p>
              <p>Obtained Via: {item.obtained_via}</p>
            </div>
          ))}
        </div>
      ) : (
        <p>No items found for this collection.</p>
      )}
    </section>
  );
}
