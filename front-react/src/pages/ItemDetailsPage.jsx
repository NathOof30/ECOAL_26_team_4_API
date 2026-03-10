import React, { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { apiFetch } from '../api/client.js';

export default function ItemDetailsPage() {
  const { id } = useParams();
  const [item, setItem] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    (async () => {
      setLoading(true);
      const res = await apiFetch(`/items/${id}`, { method: 'GET', auth: false });
      if (res.ok && res.json) {
        setItem(res.json);
      } else {
        setError('Item not found.');
      }
      setLoading(false);
    })();
  }, [id]);

  if (loading) return <p>Loading item details...</p>;
  if (error) return <p className="error">{error}</p>;
  if (!item) return null;

  return (
    <section className="card">
      <h2>{item.name}</h2>
      {item.image && (
        <img
          src={item.image}
          alt={item.name}
          style={{ maxWidth: '400px', width: '100%', height: 'auto', borderRadius: '8px', marginBottom: '1rem' }}
          onError={(e) => {
            e.target.onerror = null;
            e.target.src = 'https://placehold.co/600x400?text=No+Image'; // Fallback
          }}
        />
      )}
      <p><strong>Description:</strong> {item.description}</p>
      <p><strong>State:</strong> {item.state}</p>
      <p><strong>Acquisition Date:</strong> {item.acquisition_date}</p>
      <p><strong>Obtained Via:</strong> {item.obtained_via}</p>
      <p><strong>Estimated Value:</strong> {item.estimated_value ? `${item.estimated_value} €` : 'N/A'}</p>
      {item.collection && (
        <p>
          <strong>Part of Collection:</strong>{' '}
          <Link to={`/collections/${item.collection.id}`}>{item.collection.name}</Link>
        </p>
      )}
    </section>
  );
}
