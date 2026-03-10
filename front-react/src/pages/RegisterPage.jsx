import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext.jsx';

export default function RegisterPage() {
  const { register, loading, error, token } = useAuth();
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const navigate = useNavigate();

  async function handleSubmit(e) {
    e.preventDefault();
    await register({ name, email, password });
    if (!loading && !error) {
      if (token) {
        navigate('/');
      }
    }
  }

  return (
    <section>
      <h2>Register</h2>
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
          Créer un compte
        </button>
      </form>
      {error && <div>Erreur: {error}</div>}
    </section>
  );
}

