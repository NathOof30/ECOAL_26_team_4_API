import React from 'react';
import { Link } from 'react-router-dom';
import AuthStatus from './AuthStatus.jsx';

export default function Layout({ children }) {
  return (
    <div>
      <header>
        <h1>ECOAL React Front V1</h1>
        <nav>
          <Link to="/">Dashboard</Link> | <Link to="/collections">Collections</Link> |{' '}
          <Link to="/items">Items</Link> | <Link to="/categories">Categories</Link> |{' '}
          <Link to="/criteria">Criteria</Link> | <Link to="/scores">Scores</Link> |{' '}
          <Link to="/login">Login</Link> | <Link to="/register">Register</Link>
        </nav>
        <AuthStatus />
        <hr />
      </header>
      <main>{children}</main>
    </div>
  );
}

