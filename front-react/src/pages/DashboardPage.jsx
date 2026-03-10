import React from 'react';
import { useAuth } from '../context/AuthContext.jsx';
import ApiTesterPanel from '../components/ApiTesterPanel.jsx';

export default function DashboardPage() {
  const { user, token } = useAuth();

  return (
    <section>
      <h2>Dashboard</h2>
      <div>
        <strong>Token présent:</strong> {token ? 'oui' : 'non'}
      </div>
      <div>
        <strong>User:</strong>
        <pre>{user ? JSON.stringify(user, null, 2) : 'non chargé (essayez /user)'}</pre>
      </div>
      <ApiTesterPanel />
    </section>
  );
}

