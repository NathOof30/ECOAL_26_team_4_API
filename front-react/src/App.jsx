import React, { useEffect, useState } from 'react';
import { Route, Routes } from 'react-router-dom';
import Layout from './components/Layout.jsx';
import LogViewer from './components/LogViewer.jsx';
import { setLogCallback } from './api/client.js';
import LoginPage from './pages/LoginPage.jsx';
import RegisterPage from './pages/RegisterPage.jsx';
import HomePage from './pages/HomePage.jsx';
import ProfilePage from './pages/ProfilePage.jsx';
import EditProfilePage from './pages/EditProfilePage.jsx';
import MyCollectionPage from './pages/MyCollectionPage.jsx';
import ItemsPage from './pages/ItemsPage.jsx';
import MyItemsPage from './pages/MyItemsPage.jsx';
import CollectionsPage from './pages/CollectionsPage.jsx';
import CollectionDetailsPage from './pages/CollectionDetailsPage.jsx';
import ItemDetailsPage from './pages/ItemDetailsPage.jsx';
import ProtectedRoute from './components/ProtectedRoute.jsx';

const MAX_LOGS = 50;

export default function App() {
  const [logs, setLogs] = useState([]);
  const [lastEntry, setLastEntry] = useState(null);

  useEffect(() => {
    setLogCallback((entry) => {
      const withTs = {
        ...entry,
        timestamp: new Date().toISOString(),
      };
      setLastEntry(withTs);
      setLogs((prev) => {
        const next = [withTs, ...prev];
        if (next.length > MAX_LOGS) next.length = MAX_LOGS;
        return next;
      });
    });
  }, []);

  function handleClearLogs() {
    setLogs([]);
    setLastEntry(null);
  }

  return (
    <Layout>
      <Routes>
        <Route path="/" element={<HomePage />} />
        <Route path="/login" element={<LoginPage />} />
        <Route path="/register" element={<RegisterPage />} />
        <Route
          path="/profile"
          element={
            <ProtectedRoute>
              <ProfilePage />
            </ProtectedRoute>
          }
        />
        <Route
          path="/profile/edit"
          element={
            <ProtectedRoute>
              <EditProfilePage />
            </ProtectedRoute>
          }
        />
        <Route
          path="/items"
          element={<ItemsPage />}
        />
        <Route
          path="/items/:id"
          element={<ItemDetailsPage />}
        />
        <Route
          path="/collections"
          element={<CollectionsPage />}
        />
        <Route
          path="/collections/:id"
          element={<CollectionDetailsPage />}
        />
        <Route
          path="/my-collection"
          element={
            <ProtectedRoute>
              <MyCollectionPage />
            </ProtectedRoute>
          }
        />
        <Route
          path="/my-items"
          element={
            <ProtectedRoute>
              <MyItemsPage />
            </ProtectedRoute>
          }
        />
      </Routes>
    </Layout>
  );
}

