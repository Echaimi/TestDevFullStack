import axios from 'axios';
import type { Customer } from '../types';
import type { Order } from '../types';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:8000',
  timeout: 15_000,
  headers: {
    Accept: 'application/json',
  },
});

export const fetchCustomers = (): Promise<Customer[]> =>
  api.get<Customer[]>('/customers').then((response) => response.data);

export const fetchOrders = (customerId: number): Promise<Order[]> =>
  api.get<Order[]>(`/customers/${customerId}/orders`).then((response) => response.data);
