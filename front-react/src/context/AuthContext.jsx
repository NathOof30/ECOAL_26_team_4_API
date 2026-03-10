import React, { createContext, useContext, useEffect, useState } from 'react';
import { apiFetch, setTokenGetter } from '../api/client.js';

const STORAGE_KEY = 'ecoal_token';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [token, setToken] = useState(() => window.localStorage.getItem(STORAGE_KEY) || '');
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    setTokenGetter(() => token);
  }, [token]);

  useEffect(() => {
    if (token) {
      window.localStorage.setItem(STORAGE_KEY, token);
      fetchMe();
    } else {
      window.localStorage.removeItem(STORAGE_KEY);
      setUser(null);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [token]);

  async function fetchMe() {
    if (!token) return;
    setLoading(true);
    setError('');
    const res = await apiFetch('/user', { method: 'GET', auth: true });
    if (res.ok && res.json) {
      setUser(res.json);
    } else {
      setError('Impossible de récupérer le profil.');
    }
    setLoading(false);
  }

  async function login(email, password) {
    setLoading(true);
    setError('');
    const res = await apiFetch('/login', {
      method: 'POST',
      body: { email, password },
      auth: false,
    });
    if (res.ok && res.json?.access_token) {
      setToken(res.json.access_token);
    } else {
      setError(res.json?.message || 'Échec de la connexion.');
    }
    setLoading(false);
  }

  async function register({ name, email, password }) {
    setLoading(true);
    setError('');
    const res = await apiFetch('/register', {
      method: 'POST',
      body: {
        name,
        email,
        password,
        avatar_url: null,
        nationality: null,
        user_type: 'user',
      },
      auth: false,
    });
    if (res.ok && res.json?.access_token) {
      setToken(res.json.access_token);
    } else {
      setError(res.json?.message || 'Échec de la création de compte.');
    }
    setLoading(false);
  }

  async function logout() {
    if (!token) return;
    setLoading(true);
    setError('');
    await apiFetch('/logout', { method: 'POST', auth: true });
    setToken('');
    setUser(null);
    setLoading(false);
  }

  const value = {
    token,
    user,
    loading,
    error,
    login,
    register,
    logout,
    fetchMe,
  };

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) {
    throw new Error('useAuth must be used within AuthProvider');
  }
  return ctx;
}

