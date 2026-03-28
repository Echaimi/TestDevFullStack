import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import axios from 'axios';
import { fetchCustomers } from '../api/client';
import type { Customer } from '../types';

export default function CustomersPage() {
  const [customers, setCustomers] = useState<Customer[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const ac = new AbortController();
    let cancelled = false;
    setLoading(true);
    setError(null);
    fetchCustomers(ac.signal)
      .then((data) => {
        if (!cancelled) {
          setCustomers(Array.isArray(data) ? data : []);
        }
      })
      .catch((err) => {
        if (cancelled || axios.isCancel(err)) {
          return;
        }
        setError('Impossible de charger les clients.');
      })
      .finally(() => {
        if (!cancelled) {
          setLoading(false);
        }
      });
    return () => {
      cancelled = true;
      ac.abort();
    };
  }, []);

  return (
    <div className="page">
      <h1>Clients</h1>
      {loading && (
        <p className="loading-hint" role="status">
          Chargement…
        </p>
      )}
      {error && <p className="error">{error}</p>}
      <div className="table-wrap">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Titre</th>
              <th>Nom</th>
              <th>Prénom</th>
              <th>Code postal</th>
              <th>Ville</th>
              <th>Email</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            {customers.map((c) => (
              <tr key={c.id}>
                <td>{c.id}</td>
                <td>{c.title}</td>
                <td>{c.lastName}</td>
                <td>{c.firstName}</td>
                <td>{c.postalCode}</td>
                <td>{c.city}</td>
                <td>{c.email}</td>
                <td>
                  <Link to={`/customers/${c.id}/orders`}>Voir les commandes</Link>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
}
