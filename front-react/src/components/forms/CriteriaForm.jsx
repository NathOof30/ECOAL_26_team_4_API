import React, { useState } from 'react';
import { apiFetch } from '../../api/client.js';

export default function CriteriaForm() {
  const [name, setName] = useState('');
  const [result, setResult] = useState(null);

  async function handleSubmit(e) {
    e.preventDefault();
    const res = await apiFetch('/criteria', {
      method: 'POST',
      body: { name },
      auth: true,
    });
    setResult(res);
  }

  return (
    <section>
      <h3>Créer un critère</h3>
      <form onSubmit={handleSubmit}>
        <div>
          <label>
            Nom:{' '}
            <input
              type="text"
              value={name}
              onChange={(e) => setName(e.target.value)}
              required
            />
          </label>
        </div>
        <button type="submit">Créer critère</button>
      </form>
      <pre>{result ? JSON.stringify(result, null, 2) : '—'}</pre>
    </section>
  );
}

