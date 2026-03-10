import React, { useEffect, useState } from 'react';
import { apiFetch } from '../api/client.js';
import ScoreForm from '../components/forms/ScoreForm.jsx';

export default function ScoresPage() {
  const [scores, setScores] = useState([]);
  const [itemId, setItemId] = useState('');
  const [itemScores, setItemScores] = useState(null);

  useEffect(() => {
    (async () => {
      const res = await apiFetch('/item-criteria', { method: 'GET', auth: false });
      if (res.ok && Array.isArray(res.json)) {
        setScores(res.json);
      }
    })();
  }, []);

  async function handleFetchItemScores() {
    if (!itemId) return;
    const res = await apiFetch(`/items/${itemId}/criteria`, { method: 'GET', auth: false });
    setItemScores(res);
  }

  return (
    <section>
      <h2>Scores (item_criteria)</h2>
      <h3>Liste globale</h3>
      <pre>{JSON.stringify(scores, null, 2)}</pre>
      <h3>Scores d&apos;un item</h3>
      <div>
        <input
          type="number"
          placeholder="item id"
          value={itemId}
          onChange={(e) => setItemId(e.target.value)}
        />{' '}
        <button type="button" onClick={handleFetchItemScores}>
          GET /items/{'{id}'}/criteria
        </button>
      </div>
      <pre>{itemScores ? JSON.stringify(itemScores, null, 2) : '—'}</pre>
      <ScoreForm />
    </section>
  );
}

