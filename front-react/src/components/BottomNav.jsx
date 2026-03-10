import React from 'react';
import { NavLink } from 'react-router-dom';

export default function BottomNav() {
  return (
    <nav className="bottom-nav">
      <NavLink to="/" end className={({ isActive }) => (isActive ? 'active-link' : '')}>
        <span>Home</span>
      </NavLink>
      <NavLink
        to="/my-collection"
        className={({ isActive }) => (isActive ? 'active-link' : '')}
      >
        <span>Collection</span>
      </NavLink>
      <NavLink
        to="/items"
        className={({ isActive }) => (isActive ? 'active-link' : '')}
      >
        <span>Search</span>
      </NavLink>
      <NavLink
        to="/profile"
        className={({ isActive }) => (isActive ? 'active-link' : '')}
      >
        <span>Profile</span>
      </NavLink>
    </nav>
  );
}

