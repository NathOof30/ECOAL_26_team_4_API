import React from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext.jsx';

export default function ProfilePage() {
  const { user, logout } = useAuth();

  if (!user) {
    return (
      <section className="card">
        <h2>Profile</h2>
        <p>Loading user...</p>
      </section>
    );
  }

  return (
    <section className="card">
      <h2>Profile</h2>
      <p>
        <strong>Name:</strong> {user.name}
      </p>
      <p>
        <strong>Email:</strong> {user.email}
      </p>
      {user.nationality && (
        <p>
          <strong>Nationality:</strong> {user.nationality}
        </p>
      )}
      {user.avatar_url && (
        <p>
          <strong>Avatar URL:</strong> {user.avatar_url}
        </p>
      )}
      <div style={{ marginTop: '0.75rem' }}>
        <Link to="/profile/edit">
          <button type="button">Edit profile</button>
        </Link>{' '}
        <button type="button" onClick={logout}>
          Logout
        </button>
      </div>
    </section>
  );
}


