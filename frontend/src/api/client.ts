import axios from 'axios';
import type { Customer } from '../types';
import type { Order } from '../types';

function resolveBaseURL(): string {
  if (import.meta.env.VITE_API_BASE_URL) {
    return import.meta.env.VITE_API_BASE_URL;
  }
  if (import.meta.env.DEV) {
    return '/api';
  }
  return 'http://localhost:8000';
}

const api = axios.create({
  baseURL: resolveBaseURL(),
  timeout: 15_000,
  headers: {
    Accept: 'application/json',
  },
});

export const fetchCustomers = (signal?: AbortSignal): Promise<Customer[]> =>
  api.get<Customer[]>('/customers', { signal }).then((response) => response.data);

export const fetchOrders = (customerId: number, signal?: AbortSignal): Promise<Order[]> =>
  api.get<Order[]>(`/customers/${customerId}/orders`, { signal }).then((response) => response.data);
