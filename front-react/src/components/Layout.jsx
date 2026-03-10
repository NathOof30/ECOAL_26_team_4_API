import React from 'react';
import BottomNav from './BottomNav.jsx';

export default function Layout({ children }) {
  return (
    <div className="app-shell">
      <header className="app-header">
        <h1>ECOAL – Lighter Collections</h1>
      </header>
      <main className="app-main">
        <div className="container">{children}</div>
      </main>
      <BottomNav />
    </div>
  );
}

