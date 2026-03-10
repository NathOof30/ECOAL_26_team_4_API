import React, { useEffect, useState } from 'react';
import { apiFetch } from '../api/client.js';
import CategoryForm from '../components/forms/CategoryForm.jsx';

export default function CategoriesPage() {
  const [categories, setCategories] = useState([]);

  useEffect(() => {
    (async () => {
      const res = await apiFetch('/categories', { method: 'GET', auth: false });
      if (res.ok && Array.isArray(res.json)) {
        setCategories(res.json);
      }
    })();
  }, []);

  return (
    <section>
      <h2>Categories</h2>
      <h3>Liste</h3>
      <pre>{JSON.stringify(categories, null, 2)}</pre>
      <CategoryForm />
    </section>
  );
}

