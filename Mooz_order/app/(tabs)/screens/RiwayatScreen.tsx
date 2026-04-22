import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, SafeAreaView, StatusBar, Platform, ActivityIndicator, Alert, FlatList } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { StackNavigationProp } from '@react-navigation/stack';
import { RootStackParamList } from '../navigation/AppNavigator';
import { orderAPI } from '../../services/api';
import * as Notifications from 'expo-notifications';

type NavigationProp = StackNavigationProp<RootStackParamList, 'RiwayatScreen'>; // Pastikan ini mengarah ke RiwayatScreen

interface OrderItemDisplay {
  id: string; // ID entri laporan_penjualan
  order_id: string; // ID Pesanan asli
  date: string; // Waktu diambil (sudah diformat)
  item_name: string;
  item_price: number;
  item_quantity: number;
  item_total: number;
  customer_name: string;
}

export default function RiwayatScreen() {
  const navigation = useNavigation<NavigationProp>();
  const [completedOrders, setCompletedOrders] = useState<OrderItemDisplay[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

    console.log('RiwayatScreen rendered');

  useEffect(() => {
    const fetchOrders = async () => {
      try {
        setIsLoading(true);
        setError(null);
        console.log('Fetching completed orders...');
        const data = await orderAPI.getCompletedOrders();
        console.log('Received data:', JSON.stringify(data, null, 2));
        const now = new Date();
        const filtered = data.filter((order: OrderItemDisplay) => {
          const orderDate = new Date(order.date);
          return (now.getTime() - orderDate.getTime()) < 365 * 24 * 60 * 60 * 1000; // kurang dari 1 tahun
        });
        setCompletedOrders(filtered);
        Notifications.scheduleNotificationAsync({
          content: {
            title: "Pesanan Selesai!",
            body: `Pesanan #${filtered[filtered.length - 1].order_id} sudah selesai dan masuk ke riwayat.`,
          },
          trigger: null,
        });
      } catch (err: any) {
        console.error('Error fetching completed orders in RiwayatScreen:', err);
        setError(err.message || 'Gagal memuat riwayat pesanan.');
        Alert.alert('Error', err.message || 'Gagal memuat riwayat pesanan.');
      } finally {
        setIsLoading(false);
      }
    };

    fetchOrders();
  }, []);

  // Fungsi untuk mengelompokkan item berdasarkan order_id
  const groupOrdersByOrderId = (orders: OrderItemDisplay[]) => {
    console.log('Grouping orders:', JSON.stringify(orders, null, 2));
    const grouped: { [key: string]: { id: string; order_id: string; date: string; customer_name: string; items: { name: string; price: number; quantity: number; total: number; }[]; total_order_price: number; } } = {};
    orders.forEach(order => {
      console.log('Processing order:', order);
      if (!grouped[order.order_id]) {
        grouped[order.order_id] = {
          id: order.id,
          order_id: order.order_id,
          date: order.date,
          customer_name: order.customer_name,
          items: [],
          total_order_price: 0,
        };
      }
      grouped[order.order_id].items.push({
        name: order.item_name,
        price: order.item_price,
        quantity: order.item_quantity,
        total: order.item_total,
      });
      grouped[order.order_id].total_order_price += order.item_total;
    });
    // Urutkan pesanan berdasarkan tanggal terbaru
    const result = Object.values(grouped).sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime());
    console.log('Grouped result:', JSON.stringify(result, null, 2));
    return result;
  };

  const groupedAndSummedOrders = groupOrdersByOrderId(completedOrders);

  if (isLoading) {
    return (
      <View style={[styles.container, styles.loadingContainer]}>
        <ActivityIndicator size="large" color="#6200EE" />
        <Text style={styles.loadingText}>Memuat riwayat pesanan...</Text>
      </View>
    );
  }

  if (error) {
    return (
      <View style={[styles.container, styles.errorContainer]}>
        <Text style={styles.errorText}>{error}</Text>
        <TouchableOpacity style={styles.retryButton} onPress={() => navigation.replace('RiwayatScreen')}>
          <Text style={styles.retryButtonText}>Coba Lagi</Text>
        </TouchableOpacity>
      </View>
    );
  }

  return (
    <SafeAreaView style={styles.safeArea}>
      <StatusBar barStyle="dark-content" backgroundColor="#FFFFFF" />
      <View style={styles.header}>
        <TouchableOpacity
          style={styles.backButton}
          onPress={() => navigation.goBack()}
        >
          <Text style={styles.backButtonText}>&larr; Kembali</Text>
        </TouchableOpacity>
        <View>
          <Text style={styles.headerTitle}>Riwayat Pesanan</Text>
        </View>
      </View>

      {groupedAndSummedOrders.length === 0 ? (
        <View style={styles.emptyListContainer}>
          <Text style={styles.emptyListText}>Belum ada riwayat pesanan yang selesai.</Text>
          <Text style={styles.emptyListSubText}>Pesanan yang sudah diambil akan muncul di sini.</Text>
        </View>
      ) : (
        <FlatList
          data={groupedAndSummedOrders}
          keyExtractor={(item) => item.order_id}
          contentContainerStyle={styles.listContentContainer}
          renderItem={({ item }) => (
            <View style={styles.orderCard}>
              <View style={styles.cardHeader}>
                <Text style={styles.orderIdText}>ID Pesanan: #{item.order_id}</Text>
                <Text style={styles.dateText}>
                  {new Date(item.date).toLocaleString('id-ID', {
                    year: 'numeric', month: 'short', day: 'numeric',
                    hour: '2-digit', minute: '2-digit', hour12: false
                  })}
                </Text>
              </View>
              <Text style={styles.customerName}>Pemesan: {item.customer_name}</Text>
              
              <View style={styles.itemsListContainer}>
                <Text style={styles.itemsHeader}>Detail Produk:</Text>
                {item.items.map((product, index) => (
                  <View key={index} style={styles.itemRow}>
                    <Text style={styles.itemName}>• {product.name}</Text>
                    <Text style={styles.itemQuantity}>x{product.quantity}</Text>
                    <Text style={styles.itemPrice}>Rp {product.price.toLocaleString('id-ID')}</Text>
                  </View>
                ))}
              </View>
              
              <View style={styles.totalContainer}>
                <Text style={styles.totalLabel}>Total Pembayaran</Text>
                <Text style={styles.totalAmount}>Rp {item.total_order_price.toLocaleString('id-ID')}</Text>
              </View>
            </View>
          )}
        />
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: '#F5F7FA',
    paddingTop: Platform.OS === 'android' ? (StatusBar.currentHeight ?? 0) + 10 : 0, // Disesuaikan untuk safe area
  },
  container: {
    flex: 1,
    backgroundColor: '#F5F7FA', // Sesuaikan dengan safeArea background
  },
  loadingContainer: {
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#F5F7FA', // Background loading juga modern
  },
  loadingText: {
    marginTop: 15,
    fontSize: 16,
    color: '#555555',
    fontWeight: '500',
  },
  errorContainer: {
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#F5F7FA',
  },
  errorText: {
    fontSize: 18,
    color: '#D32F2F', // Merah yang lebih lembut
    textAlign: 'center',
    marginBottom: 20,
    fontWeight: '600',
  },
  retryButton: {
    backgroundColor: '#FF8C00', // Warna oranye konsisten
    paddingVertical: 12,
    paddingHorizontal: 25,
    borderRadius: 8,
    shadowColor: '#FF8C00',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 5,
    elevation: 5,
  },
  retryButtonText: {
    color: 'white',
    fontSize: 16,
    fontWeight: 'bold',
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingVertical: 15,
    backgroundColor: '#FFFFFF',
    borderBottomWidth: 1,
    borderBottomColor: '#E0E0E0',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.08,
    shadowRadius: 3,
    elevation: 4,
  },
  headerTitle: {
    fontSize: 22,
    fontWeight: '700',
    color: '#333333',
    marginLeft: 15,
  },
  backButton: {
    paddingVertical: 10,
    paddingHorizontal: 15,
    backgroundColor: '#FF8C00', // Oranye konsisten
    borderRadius: 8,
    shadowColor: '#FF8C00',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 6,
    elevation: 6,
  },
  backButtonText: {
    color: '#FFF',
    fontSize: 16,
    fontWeight: '600',
  },
  listContentContainer: {
    paddingHorizontal: 16,
    paddingVertical: 10,
  },
  orderCard: {
    backgroundColor: '#FFFFFF',
    padding: 20,
    borderRadius: 12,
    marginBottom: 12,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.1,
    shadowRadius: 6,
    elevation: 4,
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
    borderBottomWidth: 1,
    borderBottomColor: '#F0F0F0',
    paddingBottom: 10,
  },
  orderIdText: { // Mengubah nama style agar lebih deskriptif
    fontSize: 16,
    fontWeight: '600',
    color: '#555555',
  },
  dateText: { // Mengubah nama style agar lebih deskriptif
    fontSize: 13,
    color: '#888888',
  },
  customerName: {
    fontSize: 14,
    color: '#666666',
    marginBottom: 10,
  },
  itemsListContainer: { // Kontainer baru untuk detail produk
    marginTop: 5,
    marginBottom: 10,
  },
  itemsHeader: {
    fontSize: 15,
    fontWeight: '600',
    marginBottom: 8,
    color: '#444444',
  },
  itemRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center', // Agar teks sejajar vertikal
    marginBottom: 5,
    paddingLeft: 10,
    borderLeftWidth: 3,
    borderLeftColor: '#A7FFEB', // Warna tosca muda
    paddingVertical: 2,
  },
  itemName: {
    fontSize: 14,
    color: '#333333',
    flex: 2,
    fontWeight: '500', // Sedikit lebih tebal
  },
  itemQuantity: {
    fontSize: 14,
    color: '#666666',
    flex: 0.5,
    textAlign: 'center', // Lebih baik rata tengah
  },
  itemPrice: {
    fontSize: 14,
    color: '#FF6F00', // Oranye gelap untuk harga item
    flex: 1,
    textAlign: 'right',
    fontWeight: '600', // Sedikit lebih tebal
  },
  totalContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginTop: 15,
    paddingTop: 10,
    borderTopWidth: 1,
    borderTopColor: '#EEEEEE',
  },
  totalLabel: {
    fontSize: 16,
    fontWeight: '600',
    color: '#333333',
  },
  totalAmount: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#E65100', // Oranye gelap untuk total
  },
  emptyListContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 20,
    backgroundColor: '#FDFDFD',
    borderRadius: 10,
    marginHorizontal: 20,
    marginTop: 20,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 3,
    elevation: 2,
  },
  emptyListText: {
    fontSize: 18,
    color: '#555555',
    fontWeight: 'bold',
    marginBottom: 8,
    textAlign: 'center',
  },
  emptyListSubText: {
    fontSize: 14,
    color: '#888888',
    textAlign: 'center',
  },
});