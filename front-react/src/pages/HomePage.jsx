import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { apiFetch } from '../api/client.js';
import { useAuth } from '../context/AuthContext.jsx';

export default function HomePage() {
  const { user, token } = useAuth();
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
    <div>
      <section className="card">
        <h2>ECOAL – Lighter Collection Explorer</h2>
        <p>
          Browse and manage lighter collections, items and criteria. Explore what other
          users collect, and maintain your own personal collection.
        </p>
        {!token ? (
          <p>
            <Link to="/login">Log in</Link> or <Link to="/register">create an account</Link>{' '}
            to start your own collection.
          </p>
        ) : (
          <p>
            Welcome{user ? `, ${user.name}` : ''}! Go to{' '}
            <Link to="/my-collection">My collection</Link> or{' '}
            <Link to="/my-items">Manage my items</Link>.
          </p>
        )}
      </section>

      <section className="card">
        <h3>Featured collections</h3>
        {collections.length === 0 ? (
          <p>No collections yet.</p>
        ) : (
          <div>
            {collections.map((c) => (
              <div key={c.id} className="pill-card">
                <div className="pill-main">
                  <span className="pill-title">{c.title}</span>
                  <span className="pill-sub">
                    {c.user ? `by ${c.user.name}` : 'Unknown owner'}
                    {Array.isArray(c.items) ? ` • ${c.items.length} items` : ''}
                  </span>
                </div>
                <Link className="pill-link" to={`/collections/${c.id}`}>
                  View
                </Link>
              </div>
            ))}
          </div>
        )}
      </section>
    </div>
  );
}

