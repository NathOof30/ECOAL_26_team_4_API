import React, { useEffect, useState } from 'react';
import { apiFetch } from '../api/client.js';
import CollectionForm from '../components/forms/CollectionForm.jsx';

export default function CollectionsPage() {
  const [collections, setCollections] = useState([]);

  useEffect(() => {
    (async () => {
      const res = await apiFetch('/collections', { method: 'GET', auth: false });
      if (res.ok && Array.isArray(res.json)) {
        setCollections(res.json);
      }
    })();
  }, []);

  return (
    <section>
      <h2>Collections</h2>
      <h3>Liste</h3>
      <pre>{JSON.stringify(collections, null, 2)}</pre>
      <CollectionForm />
    </section>
  );
}

