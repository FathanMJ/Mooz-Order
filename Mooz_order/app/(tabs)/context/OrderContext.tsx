import React, { createContext, useContext, useState } from 'react';
import { ImageSourcePropType } from 'react-native';
import axios from 'axios';
import { Alert } from 'react-native';

interface Product {
  id_produk: string;
  nama_produk: string;
  harga_produk: number;
  foto_produk: string;
  keterangan_produk: string;
  kategori_produk: string;
}

interface CartItem {
  id: string;
  name: string;
  price: number;
  image: ImageSourcePropType;
  quantity: number;
  size: string;
}

interface Order {
  id: string;
  name: string;
  price: number;
  image: ImageSourcePropType;
  quantity: number;
  size: string;
}

interface APIError {
  status: number;
  message: string;
  data?: any;
}

interface CheckoutResponse {
  payment_status: string;
  data?: {
    snap_token: string;
  };
  pesan?: string;
}

interface OrderContextType {
  orders: Order[];
  addOrder: (order: Order) => void;
  updateOrderQuantity: (index: number, quantity: number) => void;
  removeOrder: (itemToRemove: { id: string; size?: string }) => void;
  clearOrders: () => void;
}

const OrderContext = createContext<OrderContextType | undefined>(undefined);

export const OrderProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [orders, setOrders] = useState<Order[]>([]);

  const addOrder = (order: Order) => {
    console.log('Mencoba menambahkan order:', order); // Log untuk debugging

    if (!order.id || !order.name || !order.price) {
      console.error('Data pesanan tidak valid:', order);
      Alert.alert('Error', 'Data produk tidak valid');
      return;
    }

    setOrders(prevOrders => {
      console.log('Previous orders:', prevOrders); // Log previous orders

      // Validasi format ID
      const itemId = order.id.toString();
      console.log('Item ID:', itemId); // Log item ID

      // Cek apakah produk sudah ada di keranjang
      const existingOrderIndex = prevOrders.findIndex(item =>
        item.id === itemId && item.size === order.size
      );

      console.log('Existing order index:', existingOrderIndex); // Log existing order index

      if (existingOrderIndex > -1) {
        // Update jumlah jika produk sudah ada
        const newOrders = [...prevOrders];
        const existingOrder = newOrders[existingOrderIndex];
        const updatedQuantity = (existingOrder.quantity || 0) + (order.quantity || 1);
        newOrders[existingOrderIndex] = { ...existingOrder, quantity: updatedQuantity };
        console.log('Update jumlah produk:', newOrders[existingOrderIndex]); // Log untuk debugging
        return newOrders;
      } else {
        // Tambah produk baru ke keranjang
        const newItem: Order = {
          ...order,
          id: itemId,
          quantity: order.quantity || 1
        };
        console.log('Menambahkan produk baru:', newItem); // Log untuk debugging
        const newOrders = [...prevOrders, newItem];
        console.log('New orders state:', newOrders); // Log new orders state
        return newOrders;
      }
    });
  };

  const updateOrderQuantity = (index: number, quantity: number) => {
    if (quantity < 1) {
      Alert.alert('Peringatan', 'Jumlah minimal adalah 1');
      return;
    }
    setOrders(prevOrders => {
      const newOrders = [...prevOrders];
      newOrders[index] = { ...newOrders[index], quantity };
      return newOrders;
    });
  };

  const removeOrder = (itemToRemove: { id: string; size?: string }) => {
    setOrders(prevOrders => prevOrders.filter(order => 
      !(order.id === itemToRemove.id && order.size === itemToRemove.size)
    ));
  };

  const clearOrders = () => {
    setOrders([]);
  };

  return (
    <OrderContext.Provider value={{ orders, addOrder, updateOrderQuantity, removeOrder, clearOrders }}>
      {children}
    </OrderContext.Provider>
  );
};

export const useOrder = () => {
  const context = useContext(OrderContext);
  if (context === undefined) {
    throw new Error('useOrder harus digunakan di dalam OrderProvider');
  }
  return context;
};