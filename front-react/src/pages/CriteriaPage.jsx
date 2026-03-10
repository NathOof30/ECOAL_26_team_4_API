import React, { useEffect, useState } from 'react';
import { apiFetch } from '../api/client.js';
import CriteriaForm from '../components/forms/CriteriaForm.jsx';

export default function CriteriaPage() {
  const [criteria, setCriteria] = useState([]);

  useEffect(() => {
    (async () => {
      const res = await apiFetch('/criteria', { method: 'GET', auth: false });
      if (res.ok && Array.isArray(res.json)) {
        setCriteria(res.json);
      }
    })();
  }, []);

  return (
    <section>
      <h2>Criteria</h2>
      <h3>Liste</h3>
      <pre>{JSON.stringify(criteria, null, 2)}</pre>
      <CriteriaForm />
    </section>
  );
}

