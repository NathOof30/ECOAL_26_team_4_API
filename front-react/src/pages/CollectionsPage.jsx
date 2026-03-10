import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { apiFetch } from '../api/client.js';
import CollectionForm from '../components/forms/CollectionForm.jsx';

export default function CollectionsPage() {
  const [collections, setCollections] = useState([]);

  useEffect(() => {
    (async () => {
      const res = await apiFetch('/collections', { method: 'GET', auth: false });
      if (res.ok && Array.isArray(res.json)) {
        setCollections(res.json);
      }
    })();
  }, []);

  return (
    <section>
      <h2>Collections</h2>
      <CollectionForm />
      
      <h3 style={{ marginTop: '2rem' }}>All Collections</h3>
      {collections.length === 0 ? (
        <p>No collections found.</p>
      ) : (
        <div className="grid">
          {collections.map((col) => (
            <div key={col.id} className="card" style={{ padding: '1rem', border: '1px solid #ccc', borderRadius: '8px' }}>
              <Link to={`/collections/${col.id}`} style={{ textDecoration: 'none', color: 'inherit' }}>
                <h4 style={{ margin: '0 0 0.5rem 0' }}>{col.name}</h4>
                <p style={{ margin: 0, color: '#666' }}>{col.description}</p>
                {col.user && (
                  <p style={{ marginTop: '0.5rem', fontSize: '0.9rem' }}>
                    By <strong>{col.user.name}</strong>
                  </p>
                )}
              </Link>
            </div>
          ))}
        </div>
      )}
    </section>
  );
}

