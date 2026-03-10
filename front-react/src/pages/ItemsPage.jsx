import React, { useEffect, useMemo, useState } from 'react';
import { Link } from 'react-router-dom';
import { apiFetch } from '../api/client.js';

export default function ItemsPage() {
  const [items, setItems] = useState([]);
  const [categories, setCategories] = useState([]);
  const [search, setSearch] = useState('');
  const [categoryFilter, setCategoryFilter] = useState('');

  useEffect(() => {
    (async () => {
      const [itemsRes, catRes] = await Promise.all([
        apiFetch('/items', { method: 'GET', auth: false }),
        apiFetch('/categories', { method: 'GET', auth: false }),
      ]);
      if (itemsRes.ok && Array.isArray(itemsRes.json)) {
        setItems(itemsRes.json);
      }
      if (catRes.ok && Array.isArray(catRes.json)) {
        setCategories(catRes.json);
      }
    })();
  }, []);

  const filtered = useMemo(() => {
    return items.filter((item) => {
      const matchesSearch =
        !search ||
        item.title?.toLowerCase().includes(search.toLowerCase()) ||
        item.description?.toLowerCase().includes(search.toLowerCase());
      const matchesCategory =
        !categoryFilter ||
        item.category1_id === Number(categoryFilter) ||
        item.category2_id === Number(categoryFilter);
      return matchesSearch && matchesCategory;
    });
  }, [items, search, categoryFilter]);

  return (
    <section className="card">
      <h2>All items</h2>
      <div>
        <label>
          Search:{' '}
          <input
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder="Search by title or description"
          />
        </label>
      </div>
      <div>
        <label>
          Category:{' '}
          <select
            value={categoryFilter}
            onChange={(e) => setCategoryFilter(e.target.value)}
          >
            <option value="">All</option>
            {categories.map((c) => (
              <option key={c.id} value={c.id}>
                {c.title}
              </option>
            ))}
          </select>
        </label>
      </div>
      {filtered.length === 0 ? (
        <p>No items found.</p>
      ) : (
        <div className="grid">
          {filtered.map((item) => (
            <div key={item.id} className="card" style={{ padding: '1rem', border: '1px solid #ccc', borderRadius: '8px' }}>
              <Link to={`/items/${item.id}`} style={{ textDecoration: 'none', color: 'inherit' }}>
                {item.image && (
                  <img
                    src={item.image}
                    alt={item.title || item.name}
                    style={{ width: '100%', height: '200px', objectFit: 'cover', borderRadius: '4px', marginBottom: '1rem' }}
                    onError={(e) => {
                      e.target.onerror = null;
                      e.target.src = 'https://placehold.co/600x400?text=No+Image';
                    }}
                  />
                )}
                <h3 style={{ margin: '0 0 0.5rem 0' }}>{item.title || item.name}</h3>
                {item.collection?.user && (
                  <p style={{ margin: 0, fontSize: '0.9rem', color: '#666' }}>
                    Owner: <em>{item.collection.user.name}</em>
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

