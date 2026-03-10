import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext.jsx';

export default function LoginPage() {
  const { login, loading, error, token } = useAuth();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const navigate = useNavigate();

  async function handleSubmit(e) {
    e.preventDefault();
    await login(email, password);
    if (!loading && !error) {
      if (token) {
        navigate('/');
      }
    }
  }

  return (
    <section>
      <h2>Login</h2>
      <form onSubmit={handleSubmit}>
        <div>
          <label>
            Email:{' '}
            <input
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
            />
          </label>
        </div>
        <div>
          <label>
            Mot de passe:{' '}
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
            />
          </label>
        </div>
        <button type="submit" disabled={loading}>
          Se connecter
        </button>
      </form>
      {error && <div>Erreur: {error}</div>}
    </section>
  );
}

