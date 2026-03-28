import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { fetchCustomers } from '../api/client';
import type { Customer } from '../types';

export default function CustomersPage() {
  const [customers, setCustomers] = useState<Customer[]>([]);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    let cancelled = false;
    fetchCustomers()
      .then((data) => {
        if (!cancelled) {
          setCustomers(Array.isArray(data) ? data : []);
        }
      })
      .catch(() => {
        if (!cancelled) {
          setError('Impossible de charger les clients.');
        }
      });
    return () => {
      cancelled = true;
    };
  }, []);

  return (
    <div className="page">
      <h1>Clients</h1>
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
