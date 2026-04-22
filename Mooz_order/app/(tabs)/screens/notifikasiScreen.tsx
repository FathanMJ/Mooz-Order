import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, RefreshControl, Alert, SafeAreaView, StatusBar, Platform } from 'react-native';
import { useNavigation, useRoute, RouteProp } from '@react-navigation/native';
import { RootStackParamList } from '../navigation/AppNavigator';
import * as Notifications from 'expo-notifications';
import { db } from '../../firebaseConfig';
import { collection, onSnapshot, query, orderBy } from 'firebase/firestore';

// Konfigurasi Notifikasi Expo
Notifications.setNotificationHandler({
  handleNotification: async () => ({
    shouldShowAlert: true,
    shouldPlaySound: true,
    shouldSetBadge: false,
  }),
});

// Request permissions at app start
async function registerForPushNotificationsAsync() {
  const { status: existingStatus } = await Notifications.getPermissionsAsync();
  let finalStatus = existingStatus;
  if (existingStatus !== 'granted') {
    const { status } = await Notifications.requestPermissionsAsync();
    finalStatus = status;
  }
  if (finalStatus !== 'granted') {
    Alert.alert('Izin Notifikasi', 'Gagal mendapatkan izin untuk notifikasi push! Anda tidak akan menerima update pesanan.');
    return;
  }
}

type NotifikasiScreenRouteProp = RouteProp<RootStackParamList, 'NotifikasiScreen'>;

interface OrderItem {
  id: string;
  name: string;
  price: number;
  quantity?: number;
  size?: string;
}

interface Notification {
  id: string;
  order_id: string;
  message: string;
  time: string;
  status: 'menunggu pembayaran' | 'sudah dibayar' | 'dalam antrian' | 'proses pembuatan' | 'siap diambil' | 'sudah diambil';
  orderedItems: OrderItem[];
  total: number;
  catatan?: string; // Tambahkan field catatan
}

interface NotifikasiScreenProps {
  navigation: any;
  route: NotifikasiScreenRouteProp;
}

const NotifikasiScreen = () => {
  const navigation = useNavigation();
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [refreshing, setRefreshing] = useState(false);

  useEffect(() => {
    const q = query(collection(db, 'notifications'), orderBy('time', 'desc'));
    const unsubscribe = onSnapshot(q, (snapshot) => {
      const notifList = snapshot.docs.map(
        doc => ({ ...(doc.data() as Notification), id: doc.id })
      );
      setNotifications(notifList);
    });
    return () => unsubscribe();
  }, []);

  const handleGoBack = () => navigation.goBack();

  const showLocalNotification = (notification: Notification) => {
    const statusMessages = {
      'menunggu pembayaran': 'Menunggu pembayaran',
      'sudah dibayar': 'Pembayaran berhasil',
      'dalam antrian': 'Pesanan Anda telah masuk dalam antrian',
      'proses pembuatan': 'Pesanan Anda sedang diproses',
      'siap diambil': 'Pesanan Anda siap diambil',
      'sudah diambil': 'Pesanan Anda telah diambil'
    };

    const message = statusMessages[notification.status] || notification.message;

    Notifications.scheduleNotificationAsync({
      content: {
        title: "Update Status Pesanan Anda",
        body: `Pesanan #${notification.order_id}: ${message}`,
        sound: 'default',
        data: notification,
      },
      trigger: null,
    })
    .then(id => console.log('Local notification scheduled with id:', id))
    .catch(err => console.error('Error scheduling local notification:', err));
  };

  const showNewOrderNotification = (newOrder: any) => {
    const statusMessages: { [key: string]: string } = {
      'menunggu pembayaran': 'Menunggu pembayaran',
      'sudah dibayar': 'Pembayaran berhasil',
      'dalam antrian': 'Pesanan Anda telah masuk dalam antrian',
      'proses pembuatan': 'Pesanan Anda sedang diproses',
      'siap diambil': 'Pesanan Anda siap diambil',
      'sudah diambil': 'Pesanan Anda telah diambil'
    };

    const message = statusMessages[newOrder.status] || 'Pesanan baru dibuat';
    const catatanText = newOrder.catatan ? `\nCatatan: ${newOrder.catatan}` : '';

    Notifications.scheduleNotificationAsync({
      content: {
        title: "Pesanan Baru Dibuat",
        body: `${message}. Total: Rp ${newOrder.total.toLocaleString('id-ID')}${catatanText}`,
        sound: 'default',
        data: newOrder,
      },
      trigger: null,
    })
    .then(id => console.log('New order notification scheduled with id:', id))
    .catch(err => console.error('Error scheduling new order notification:', err));
  };

  const getStatusStyle = (status: Notification['status']): object => {
    switch (status) {
      case 'menunggu pembayaran':
        return styles.pendingStatus;
      case 'sudah dibayar':
        return styles.paidStatus;
      case 'dalam antrian':
        return styles.queueStatus;
      case 'proses pembuatan':
        return styles.processingStatus;
      case 'siap diambil':
        return styles.readyStatus;
      case 'sudah diambil':
        return styles.completedStatus;
      default:
        return {};
    }
  };

  return (
    <SafeAreaView style={styles.safeArea}>
      <StatusBar barStyle="dark-content" backgroundColor="#FFFFFF" />
      <View style={styles.header}>
        <TouchableOpacity style={styles.backButton} onPress={handleGoBack}>
          <Text style={styles.backButtonText}>&larr; Kembali</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Notifikasi Pesanan</Text>
      </View>

      {notifications.length === 0 && !refreshing ? (
        <View style={styles.emptyListContainer}>
          <Text style={styles.emptyListText}>Tidak ada notifikasi pesanan.</Text>
          <Text style={styles.emptyListSubText}>Pesanan baru akan muncul di sini.</Text>
        </View>
      ) : null}

      <FlatList
        data={notifications}
        keyExtractor={(item) => item.id}
        contentContainerStyle={styles.listContentContainer}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={() => setRefreshing(true)}
            colors={['#4CAF50']} // Warna hijau untuk refresh indicator
            tintColor={'#4CAF50'}
          />
        }
        renderItem={({ item }) => (
          <View style={styles.notificationCard}>
            <View style={styles.cardHeader}>
              <Text style={styles.orderId}># {item.order_id}</Text>
              <View style={[styles.statusBadge, getStatusStyle(item.status)]}>
                <Text style={styles.statusBadgeText}>
                  {item.status.toUpperCase()}
                </Text>
              </View>
            </View>
            <Text style={styles.message}>{item.message}</Text>
            <Text style={styles.time}>{new Date(item.time).toLocaleString('id-ID', {
              year: 'numeric', month: 'short', day: 'numeric',
              hour: '2-digit', minute: '2-digit', hour12: false
            })}</Text>

            <View style={styles.orderDetails}>
              <Text style={styles.orderTitle}>Detail Pesanan:</Text>
              {Array.isArray(item.orderedItems) && item.orderedItems.map((orderItem: OrderItem, index: number) => (
                <View key={`${orderItem.id}-${orderItem.size || ''}-${index}`} style={styles.orderItemContainer}>
                  <Text style={styles.orderItemName}>{orderItem.name}</Text>
                  <View style={styles.orderItemDetails}>
                    {orderItem.size && (
                      <Text style={styles.orderItemText}>Ukuran: {orderItem.size}</Text>
                    )}
                    <Text style={styles.orderItemText}>Jumlah: {orderItem.quantity || 1}</Text>
                    <Text style={styles.orderItemText}>Harga: Rp {orderItem.price.toLocaleString('id-ID')}</Text>
                    <Text style={styles.orderItemSubtotal}>
                      Subtotal: Rp {(orderItem.price * (orderItem.quantity || 1)).toLocaleString('id-ID')}
                    </Text>
                  </View>
                </View>
              ))}
              <View style={styles.totalContainer}>
                <Text style={styles.totalLabel}>Total Pembayaran</Text>
                <Text style={styles.totalAmount}>Rp {item.total.toLocaleString('id-ID')}</Text>
              </View>
              
              {/* Tampilkan catatan jika ada */}
              {item.catatan && (
                <View style={styles.catatanContainer}>
                  <Text style={styles.catatanLabel}>Catatan:</Text>
                  <Text style={styles.catatanText}>{item.catatan}</Text>
                </View>
              )}
            </View>
          </View>
        )}
      />
    </SafeAreaView>
  );
};

const NotifikasiScreenWithNavigation = () => {
  const navigation = useNavigation();
  const route = useRoute<NotifikasiScreenRouteProp>();

  return (
    <NotifikasiScreen />
  );
};

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: '#F5F7FA', // Warna latar belakang yang lebih terang dan modern
    // Perbaikan utama di sini: tambahkan nilai default 0 jika currentHeight undefined
    paddingTop: Platform.OS === 'android' ? (StatusBar.currentHeight ?? 0) + 10 : 0, 
    // Saya sarankan nilai default 20 untuk memastikan ada jarak bahkan jika currentHeight null/0
    // paddingTop: Platform.OS === 'android' ? (StatusBar.currentHeight ?? 0) + 20 : 0, 
  },
  container: {
    flex: 1,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingVertical: 15,
    backgroundColor: '#FFFFFF', // Header putih bersih
    borderBottomWidth: 1,
    borderBottomColor: '#E0E0E0', // Garis bawah lebih halus
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.08, // Bayangan lebih halus
    shadowRadius: 3,
    elevation: 4,
    // marginTop: 20, // Baris ini sudah dihapus di iterasi sebelumnya, pastikan tetap dihapus
  },
  headerTitle: {
    fontSize: 22,
    fontWeight: '700', // Lebih tebal dari 'bold'
    color: '#333333', // Warna teks yang lebih gelap
    marginLeft: 15,
  },
  backButton: {
    paddingVertical: 10,
    paddingHorizontal: 15,
    backgroundColor: '#FF8C00', // Warna oranye untuk tombol kembali
    borderRadius: 8,
    shadowColor: '#FF8C00', // Sesuaikan warna bayangan dengan tombol
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3, // Sedikit lebih menonjol
    shadowRadius: 6,
    elevation: 6,
  },
  backButtonText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '600',
  },
  listContentContainer: {
    paddingHorizontal: 16,
    paddingVertical: 10,
  },
  notificationCard: {
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
  orderId: {
    fontSize: 16,
    fontWeight: '600',
    color: '#555555',
  },
  message: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333333',
    marginBottom: 8,
  },
  time: {
    fontSize: 13,
    color: '#888888',
    marginBottom: 10,
  },
  statusBadge: {
    paddingVertical: 5,
    paddingHorizontal: 12,
    borderRadius: 20,
    minWidth: 100, // Menyesuaikan lebar minimum
    alignItems: 'center',
  },
  statusBadgeText: {
    fontSize: 12,
    fontWeight: 'bold',
    color: '#FFFFFF',
  },
  pendingStatus: {
    backgroundColor: '#FFAB00', // Oranye terang
  },
  paidStatus: {
    backgroundColor: '#4CAF50', // Hijau untuk pembayaran berhasil
  },
  queueStatus: {
    backgroundColor: '#00B0FF', // Biru cerah
  },
  processingStatus: {
    backgroundColor: '#FF6F00', // Oranye gelap
  },
  readyStatus: {
    backgroundColor: '#4CAF50', // Hijau solid
  },
  completedStatus: {
    backgroundColor: '#6A1B9A', // Ungu gelap
  },
  orderDetails: {
    marginTop: 10,
    paddingTop: 10,
    borderTopWidth: 1,
    borderTopColor: '#EEEEEE',
  },
  orderTitle: {
    fontSize: 15,
    fontWeight: '600',
    marginBottom: 8,
    color: '#444444',
  },
  orderItemContainer: {
    flexDirection: 'column',
    marginBottom: 8,
    paddingLeft: 12,
    borderLeftWidth: 3,
    borderLeftColor: '#A7FFEB', // Warna tosca muda
    paddingVertical: 4,
  },
  orderItemName: {
    fontSize: 15,
    fontWeight: '500',
    color: '#333333',
    marginBottom: 2,
  },
  orderItemDetails: {
    marginLeft: 15,
  },
  orderItemText: {
    fontSize: 13,
    color: '#666666',
    marginBottom: 1,
  },
  orderItemSubtotal: {
    fontSize: 14,
    fontWeight: 'bold',
    color: '#444444',
    marginTop: 4,
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
    backgroundColor: '#FDFDFD', // Latar belakang putih untuk kontainer kosong
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
  catatanContainer: {
    marginTop: 15,
    paddingTop: 10,
    borderTopWidth: 1,
    borderTopColor: '#EEEEEE',
  },
  catatanLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: '#666666',
    marginBottom: 5,
  },
  catatanText: {
    fontSize: 14,
    color: '#333333',
    fontStyle: 'italic',
    backgroundColor: '#F8F9FA',
    padding: 10,
    borderRadius: 8,
    borderLeftWidth: 3,
    borderLeftColor: '#FF8C00',
  },
});

export default NotifikasiScreenWithNavigation;