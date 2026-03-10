import React, { useEffect, useState } from 'react';
import { useAuth } from '../context/AuthContext.jsx';
import { apiFetch } from '../api/client.js';
import CollectionForm from '../components/forms/CollectionForm.jsx';

export default function MyCollectionPage() {
  const { user } = useAuth();
  const [collection, setCollection] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!user) return;
    (async () => {
      setLoading(true);
      const res = await apiFetch('/collections', { method: 'GET', auth: false });
      if (res.ok && Array.isArray(res.json)) {
        const mine = res.json.find((c) => c.user_id === user.id || c.user?.id === user.id);
        setCollection(mine || null);
      }
      setLoading(false);
    })();
  }, [user]);

  async function refresh() {
    if (!user) return;
    const res = await apiFetch('/collections', { method: 'GET', auth: false });
    if (res.ok && Array.isArray(res.json)) {
      const mine = res.json.find((c) => c.user_id === user.id || c.user?.id === user.id);
      setCollection(mine || null);
    }
  }

  if (!user) {
    return (
      <section className="card">
        <h2>My collection</h2>
        <p>You must be logged in.</p>
      </section>
    );
  }

  if (loading) {
    return (
      <section className="card">
        <h2>My collection</h2>
        <p>Loading...</p>
      </section>
    );
  }

  return (
    <div>
      <section className="card">
        <h2>My collection</h2>
        {!collection ? (
          <>
            <p>You do not have a collection yet. Create one below.</p>
            <CollectionForm />
          </>
        ) : (
          <>
            <p>
              <strong>{collection.title}</strong>
            </p>
            {collection.description && <p>{collection.description}</p>}
            {Array.isArray(collection.items) && (
              <>
                <h3>Items</h3>
                <ul>
                  {collection.items.map((item) => (
                    <li key={item.id}>{item.title}</li>
                  ))}
                </ul>
              </>
            )}
            <button type="button" onClick={refresh}>
              Refresh collection
            </button>
          </>
        )}
      </section>
    </div>
  );
}

