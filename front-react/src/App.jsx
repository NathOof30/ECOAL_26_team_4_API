import React, { useEffect, useState } from 'react';
import { Route, Routes } from 'react-router-dom';
import Layout from './components/Layout.jsx';
import LogViewer from './components/LogViewer.jsx';
import { setLogCallback } from './api/client.js';
import LoginPage from './pages/LoginPage.jsx';
import RegisterPage from './pages/RegisterPage.jsx';
import DashboardPage from './pages/DashboardPage.jsx';
import CollectionsPage from './pages/CollectionsPage.jsx';
import ItemsPage from './pages/ItemsPage.jsx';
import CategoriesPage from './pages/CategoriesPage.jsx';
import CriteriaPage from './pages/CriteriaPage.jsx';
import ScoresPage from './pages/ScoresPage.jsx';
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
        <Route
          path="/"
          element={
            <ProtectedRoute>
              <DashboardPage />
            </ProtectedRoute>
          }
        />
        <Route path="/login" element={<LoginPage />} />
        <Route path="/register" element={<RegisterPage />} />
        <Route
          path="/collections"
          element={
            <ProtectedRoute>
              <CollectionsPage />
            </ProtectedRoute>
          }
        />
        <Route
          path="/items"
          element={
            <ProtectedRoute>
              <ItemsPage />
            </ProtectedRoute>
          }
        />
        <Route
          path="/categories"
          element={
            <ProtectedRoute>
              <CategoriesPage />
            </ProtectedRoute>
          }
        />
        <Route
          path="/criteria"
          element={
            <ProtectedRoute>
              <CriteriaPage />
            </ProtectedRoute>
          }
        />
        <Route
          path="/scores"
          element={
            <ProtectedRoute>
              <ScoresPage />
            </ProtectedRoute>
          }
        />
      </Routes>
      <LogViewer logs={logs} lastEntry={lastEntry} onClear={handleClearLogs} />
    </Layout>
  );
}

