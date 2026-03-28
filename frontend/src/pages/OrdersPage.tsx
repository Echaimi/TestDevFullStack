import { useEffect, useMemo, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import axios from 'axios';
import { fetchOrders } from '../api/client';
import type { Order } from '../types';

export default function OrdersPage() {
  const { id } = useParams<{ id: string }>();
  const customerId = id ? Number.parseInt(id, 10) : NaN;
  const [orders, setOrders] = useState<Order[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    if (Number.isNaN(customerId)) {
      setLoading(false);
      return;
    }
    const ac = new AbortController();
    let cancelled = false;
    setLoading(true);
    setError(null);
    setOrders([]);
    fetchOrders(customerId, ac.signal)
      .then((data) => {
        if (!cancelled) {
          if (Array.isArray(data)) {
            setOrders(data);
          } else {
            setError('Réponse API inattendue.');
          }
        }
      })
      .catch((err) => {
        if (cancelled || axios.isCancel(err)) {
          return;
        }
        setError('Impossible de charger les commandes.');
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
  }, [customerId]);

  const total = useMemo(
    () => orders.reduce((sum, o) => sum + (Number.isFinite(o.price) ? o.price : 0), 0),
    [orders],
  );

  return (
    <div className="page">
      <p>
        <Link to="/">← Retour à la liste des clients</Link>
      </p>
      <h1>Commandes du client #{id}</h1>
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
              <th>Nom</th>
              <th>ID achat</th>
              <th>ID produit</th>
              <th>Quantité</th>
              <th>Prix</th>
              <th>Devise</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            {orders.map((o) => (
              <tr key={`${o.purchaseIdentifier}-${o.productId}-${o.date}`}>
                <td>{o.lastName}</td>
                <td>{o.purchaseIdentifier}</td>
                <td>{o.productId}</td>
                <td>{o.quantity}</td>
                <td>{o.price}</td>
                <td>{o.currency}</td>
                <td>{o.date}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
      <p className="total">
        <strong>Total :</strong> {total.toFixed(2)}
      </p>
    </div>
  );
}
