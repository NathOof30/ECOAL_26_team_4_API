import React, { useEffect, useState } from 'react';
import { useAuth } from '../context/AuthContext.jsx';
import { apiFetch } from '../api/client.js';
import { useNavigate } from 'react-router-dom';

export default function MyItemsPage() {
  const { user } = useAuth();
  const [items, setItems] = useState([]);
  const navigate = useNavigate();

  useEffect(() => {
    if (!user) return;
    (async () => {
      const res = await apiFetch('/items', { method: 'GET', auth: false });
      if (res.ok && Array.isArray(res.json)) {
        const mine = res.json.filter(
          (item) =>
            item.collection?.user_id === user.id ||
            item.collection?.user?.id === user.id,
        );
        setItems(mine);
      }
    })();
  }, [user]);

  if (!user) {
    return (
      <section className="card">
        <h2>My items</h2>
        <p>You must be logged in.</p>
      </section>
    );
  }

  return (
    <section className="card">
      <h2>My items</h2>
      <div style={{ marginBottom: '0.75rem' }}>
        <button type="button" onClick={() => navigate('/my-collection/add-item')}>
          Add item
        </button>{' '}
        <button type="button" onClick={() => navigate('/my-collection/edit-items')}>
          Edit items
        </button>
      </div>
      {items.length === 0 ? (
        <p>You do not have any items yet.</p>
      ) : (
        <div>
          {items.map((item) => (
            <div key={item.id} className="pill-card">
              <div className="pill-main">
                <span className="pill-title">{item.title}</span>
                {item.description && (
                  <span className="pill-sub">{item.description.slice(0, 50)}</span>
                )}
              </div>
            </div>
          ))}
        </div>
      )}
    </section>
  );
}

