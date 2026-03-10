import React, { useState } from 'react';
import { apiFetch } from '../../api/client.js';

export default function CategoryForm() {
  const [title, setTitle] = useState('');
  const [result, setResult] = useState(null);

  async function handleSubmit(e) {
    e.preventDefault();
    const res = await apiFetch('/categories', {
      method: 'POST',
      body: { title },
      auth: true,
    });
    setResult(res);
  }

  return (
    <section>
      <h3>Créer une catégorie</h3>
      <form onSubmit={handleSubmit}>
        <div>
          <label>
            Titre:{' '}
            <input
              type="text"
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              required
            />
          </label>
        </div>
        <button type="submit">Créer catégorie</button>
      </form>
      <pre>{result ? JSON.stringify(result, null, 2) : '—'}</pre>
    </section>
  );
}

