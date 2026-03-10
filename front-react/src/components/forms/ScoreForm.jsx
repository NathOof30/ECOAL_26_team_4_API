import React, { useState } from 'react';
import { apiFetch } from '../../api/client.js';

export default function ScoreForm() {
  const [itemId, setItemId] = useState('');
  const [criteriaId, setCriteriaId] = useState('');
  const [value, setValue] = useState('2');
  const [result, setResult] = useState(null);

  async function handleSubmit(e) {
    e.preventDefault();
    const res = await apiFetch('/item-criteria', {
      method: 'POST',
      body: {
        id_item: Number(itemId),
        id_criteria: Number(criteriaId),
        value: Number(value),
      },
      auth: true,
    });
    setResult(res);
  }

  return (
    <section>
      <h3>Créer un score item_criteria</h3>
      <form onSubmit={handleSubmit}>
        <div>
          <label>
            id_item:{' '}
            <input
              type="number"
              value={itemId}
              onChange={(e) => setItemId(e.target.value)}
              required
            />
          </label>
        </div>
        <div>
          <label>
            id_criteria:{' '}
            <input
              type="number"
              value={criteriaId}
              onChange={(e) => setCriteriaId(e.target.value)}
              required
            />
          </label>
        </div>
        <div>
          <label>
            value (0,1,2):{' '}
            <input
              type="number"
              min="0"
              max="2"
              value={value}
              onChange={(e) => setValue(e.target.value)}
              required
            />
          </label>
        </div>
        <button type="submit">Créer score</button>
      </form>
      <pre>{result ? JSON.stringify(result, null, 2) : '—'}</pre>
    </section>
  );
}

