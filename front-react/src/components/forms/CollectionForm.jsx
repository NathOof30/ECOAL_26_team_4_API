import React, { useState } from 'react';
import { apiFetch } from '../../api/client.js';

export default function CollectionForm() {
  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [result, setResult] = useState(null);

  async function handleSubmit(e) {
    e.preventDefault();
    const res = await apiFetch('/collections', {
      method: 'POST',
      body: { title, description: description || null },
      auth: true,
    });
    setResult(res);
  }

  return (
    <section>
      <h3>Créer la collection de l&apos;utilisateur courant</h3>
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
        <div>
          <label>
            Description:{' '}
            <input
              type="text"
              value={description}
              onChange={(e) => setDescription(e.target.value)}
            />
          </label>
        </div>
        <button type="submit">Créer collection</button>
      </form>
      <pre>{result ? JSON.stringify(result, null, 2) : '—'}</pre>
    </section>
  );
}

