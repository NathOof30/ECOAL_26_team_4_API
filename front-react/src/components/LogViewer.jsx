import React from 'react';

export default function LogViewer({ logs, lastEntry, onClear }) {
  return (
    <section>
      <h2>Log des requêtes</h2>
      <button type="button" onClick={onClear}>
        Clear log
      </button>
      <div>
        {logs.length === 0 ? (
          <div>Aucune requête.</div>
        ) : (
          <ul>
            {logs.map((log, index) => (
              <li key={index}>
                [{log.timestamp}] {log.method} {log.url} → {log.status} (
                {log.durationMs.toFixed(1)} ms)
              </li>
            ))}
          </ul>
        )}
      </div>
      <h3>Dernière réponse détaillée</h3>
      {lastEntry ? (
        <>
          <pre>
            {JSON.stringify(
              {
                request: {
                  url: lastEntry.url,
                  method: lastEntry.method,
                  headers: lastEntry.headersSent,
                  body: lastEntry.bodySent,
                },
                response: {
                  ok: lastEntry.ok,
                  status: lastEntry.status,
                  durationMs: lastEntry.durationMs.toFixed(1),
                  error: lastEntry.error || null,
                },
              },
              null,
              2,
            )}
          </pre>
          <pre>{lastEntry.json ? JSON.stringify(lastEntry.json, null, 2) : 'JSON: null'}</pre>
          <pre>
            {lastEntry.text !== null && lastEntry.text !== undefined
              ? lastEntry.text
              : 'Texte brut: null'}
          </pre>
        </>
      ) : (
        <div>Aucune réponse encore.</div>
      )}
    </section>
  );
}

