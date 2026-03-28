import { render, screen, waitFor } from '@testing-library/react';
import { BrowserRouter } from 'react-router-dom';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import * as client from '../api/client';
import CustomersPage from './CustomersPage';

vi.mock('../api/client', () => ({
  fetchCustomers: vi.fn(),
  fetchOrders: vi.fn(),
}));

describe('CustomersPage', () => {
  afterEach(() => {
    vi.clearAllMocks();
  });

  beforeEach(() => {
    vi.mocked(client.fetchCustomers).mockResolvedValue([
      {
        id: 1,
        title: 'mme',
        lastName: 'Dupont',
        firstName: 'Marie',
        postalCode: '75001',
        city: 'Paris',
        email: 'marie@example.com',
      },
    ]);
  });

  it('affiche les clients renvoyés par l’API', async () => {
    render(
      <BrowserRouter>
        <CustomersPage />
      </BrowserRouter>,
    );

    await waitFor(() => {
      expect(screen.getByText('Dupont')).toBeInTheDocument();
    });
    expect(screen.getByRole('link', { name: /voir les commandes/i })).toHaveAttribute(
      'href',
      '/customers/1/orders',
    );
    expect(client.fetchCustomers).toHaveBeenCalledOnce();
  });
});
