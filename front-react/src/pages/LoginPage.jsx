import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext.jsx';

export default function LoginPage() {
  const { login, loading, error, token } = useAuth();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const navigate = useNavigate();

  async function handleSubmit(e) {
    e.preventDefault();
    await login(email, password);
    if (token) {
      navigate('/');
    }
  }

  return (
    <div className="auth-screen">
      <div>
        <div className="auth-hero-title">Light It</div>
        <div className="auth-hero-sub">Bring the light to your collection</div>
      </div>
      <div className="auth-card">
        <h2>Login</h2>
        <form onSubmit={handleSubmit}>
          <div>
            <label>
              Email
              <br />
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
              Password
              <br />
              <input
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
              />
            </label>
          </div>
          <button type="submit" disabled={loading}>
            Login
          </button>
        </form>
        {error && <div className="auth-footer-text">Error: {error}</div>}
        <div className="auth-footer-text">Forgot your password?</div>
        <div className="auth-footer-text">
          Don&apos;t have an account? <Link to="/register">Become a collector</Link>
        </div>
      </div>
    </div>
  );
}

