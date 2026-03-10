import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext.jsx';
import { apiFetch } from '../api/client.js';

export default function EditProfilePage() {
  const { user, fetchMe } = useAuth();
  const navigate = useNavigate();
  const [name, setName] = useState(user?.name || '');
  const [email, setEmail] = useState(user?.email || '');
  const [avatarUrl, setAvatarUrl] = useState(user?.avatar_url || '');
  const [nationality, setNationality] = useState(user?.nationality || '');
  const [password, setPassword] = useState('');
  const [message, setMessage] = useState('');

  if (!user) {
    return (
      <section className="card">
        <h2>Edit profile</h2>
        <p>Loading user...</p>
      </section>
    );
  }

  async function handleSubmit(e) {
    e.preventDefault();
    const body = {
      name,
      email,
      avatar_url: avatarUrl || null,
      nationality: nationality || null,
    };
    if (password) {
      body.password = password;
    }
    const res = await apiFetch(`/users/${user.id}`, {
      method: 'PUT',
      body,
      auth: true,
    });
    if (res.ok) {
      setMessage('Profile updated.');
      await fetchMe();
      navigate('/profile');
    } else {
      setMessage('Update failed.');
    }
  }

  return (
    <section className="card">
      <h2>Edit profile</h2>
      <form onSubmit={handleSubmit}>
        <div>
          <label>
            Name
            <br />
            <input value={name} onChange={(e) => setName(e.target.value)} required />
          </label>
        </div>
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
            Avatar URL
            <br />
            <input
              value={avatarUrl}
              onChange={(e) => setAvatarUrl(e.target.value)}
              placeholder="https://..."
            />
          </label>
        </div>
        <div>
          <label>
            Nationality
            <br />
            <input
              value={nationality}
              onChange={(e) => setNationality(e.target.value)}
              placeholder="Optional"
            />
          </label>
        </div>
        <div>
          <label>
            New password (optional)
            <br />
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
            />
          </label>
        </div>
        <button type="submit">Save changes</button>
      </form>
      {message && <p>{message}</p>}
    </section>
  );
}

