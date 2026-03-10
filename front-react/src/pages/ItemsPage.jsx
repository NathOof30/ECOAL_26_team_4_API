import React, { useEffect, useState } from 'react';
import { apiFetch } from '../api/client.js';
import ItemForm from '../components/forms/ItemForm.jsx';

export default function ItemsPage() {
  const [items, setItems] = useState([]);

  useEffect(() => {
    (async () => {
      const res = await apiFetch('/items', { method: 'GET', auth: false });
      if (res.ok && Array.isArray(res.json)) {
        setItems(res.json);
      }
    })();
  }, []);

  return (
    <section>
      <h2>Items</h2>
      <h3>Liste</h3>
      <pre>{JSON.stringify(items, null, 2)}</pre>
      <ItemForm />
    </section>
  );
}

