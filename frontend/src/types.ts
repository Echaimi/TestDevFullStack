export interface Customer {
  id: number;
  title: string;
  lastName: string;
  firstName: string;
  postalCode: string;
  city: string;
  email: string;
}

export interface Order {
  lastName: string;
  purchaseIdentifier: string;
  productId: string;
  quantity: number;
  price: number;
  currency: string;
  date: string;
}
