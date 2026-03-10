import React, { useState } from 'react';
import { apiFetch } from '../../api/client.js';

export default function ItemForm() {
  const [title, setTitle] = useState('');
  const [description, setDescription] = useState('');
  const [imageUrl, setImageUrl] = useState('');
  const [status, setStatus] = useState(false);
  const [category1Id, setCategory1Id] = useState('');
  const [category2Id, setCategory2Id] = useState('');
  const [result, setResult] = useState(null);

  async function handleSubmit(e) {
    e.preventDefault();
    const body = {
      title,
      description: description || null,
      image_url: imageUrl || null,
      status: !!status,
      category1_id: category1Id ? Number(category1Id) : null,
      category2_id: category2Id ? Number(category2Id) : null,
    };
    const res = await apiFetch('/items', {
      method: 'POST',
      body,
      auth: true,
    });
    setResult(res);
  }

  return (
    <section>
      <h3>Créer un item</h3>
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
        <div>
          <label>
            Image URL:{' '}
            <input
              type="text"
              value={imageUrl}
              onChange={(e) => setImageUrl(e.target.value)}
            />
          </label>
        </div>
        <div>
          <label>
            <input
              type="checkbox"
              checked={status}
              onChange={(e) => setStatus(e.target.checked)}
            />{' '}
            Status (publié)
          </label>
        </div>
        <div>
          <label>
            category1_id:{' '}
            <input
              type="number"
              value={category1Id}
              onChange={(e) => setCategory1Id(e.target.value)}
              required
            />
          </label>
        </div>
        <div>
          <label>
            category2_id (optionnel):{' '}
            <input
              type="number"
              value={category2Id}
              onChange={(e) => setCategory2Id(e.target.value)}
            />
          </label>
        </div>
        <button type="submit">Créer item</button>
      </form>
      <pre>{result ? JSON.stringify(result, null, 2) : '—'}</pre>
    </section>
  );
}

