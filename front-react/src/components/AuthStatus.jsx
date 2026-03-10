import React from 'react';
import { useAuth } from '../context/AuthContext.jsx';

export default function AuthStatus() {
  const { user, token, logout, fetchMe, loading } = useAuth();

  return (
    <section>
      <h2>Auth</h2>
      {token ? (
        <div>
          <div>Connecté avec un token.</div>
          <div>
            <button type="button" onClick={fetchMe} disabled={loading}>
              Rafraîchir /user
            </button>{' '}
            <button type="button" onClick={logout} disabled={loading}>
              Logout
            </button>
          </div>
          <div>
            <strong>Token:</strong>{' '}
            <code>{token.slice(0, 16)}{token.length > 16 ? '...' : ''}</code>
          </div>
          <div>
            <strong>User:</strong>{' '}
            <pre>{user ? JSON.stringify(user, null, 2) : 'non chargé'}</pre>
          </div>
        </div>
      ) : (
        <div>Pas de token. Veuillez vous connecter.</div>
      )}
    </section>
  );
}

