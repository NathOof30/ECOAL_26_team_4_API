import React, { useState } from 'react';
import { apiFetch } from '../api/client.js';

export default function ApiTesterPanel() {
  const [method, setMethod] = useState('GET');
  const [path, setPath] = useState('/items');
  const [body, setBody] = useState('');
  const [useAuth, setUseAuth] = useState(false);
  const [lastResult, setLastResult] = useState(null);

  async function handleSend() {
    let parsed = null;
    if (body.trim()) {
      try {
        parsed = JSON.parse(body);
      } catch (e) {
        alert('Body JSON invalide: ' + e.message);
        return;
      }
    }
    const res = await apiFetch(path, {
      method,
      body: parsed,
      auth: useAuth,
    });
    setLastResult(res);
  }

  return (
    <section>
      <h2>Testeur rapide d&apos;API</h2>
      <div>
        Méthode:{' '}
        <select value={method} onChange={(e) => setMethod(e.target.value)}>
          <option>GET</option>
          <option>POST</option>
          <option>PUT</option>
          <option>DELETE</option>
        </select>
      </div>
      <div>
        Path relatif:{' '}
        <input
          type="text"
          value={path}
          onChange={(e) => setPath(e.target.value)}
          size={40}
        />
      </div>
      <div>
        <label>
          <input
            type="checkbox"
            checked={useAuth}
            onChange={(e) => setUseAuth(e.target.checked)}
          />{' '}
          Utiliser Authorization Bearer
        </label>
      </div>
      <div>
        Body JSON (optionnel):
        <br />
        <textarea
          rows={4}
          cols={60}
          value={body}
          onChange={(e) => setBody(e.target.value)}
        />
      </div>
      <button type="button" onClick={handleSend}>
        Envoyer
      </button>
      <div>
        <h3>Dernier résultat local (sans log global)</h3>
        <pre>{lastResult ? JSON.stringify(lastResult, null, 2) : '—'}</pre>
      </div>
    </section>
  );
}

