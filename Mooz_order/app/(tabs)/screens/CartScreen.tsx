import React, { Component } from 'react';
import {
  View, Text, StyleSheet, Image, TouchableOpacity,
  SafeAreaView, ScrollView, Alert, TextInput,
  ActivityIndicator // Import ActivityIndicator untuk indikator loading
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import { ImageSourcePropType } from 'react-native';
import { useOrder } from '../context/OrderContext';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { RootStackParamList } from '../navigation/AppNavigator';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { WebView } from 'react-native-webview';
import api from '../../services/api';

interface OrderItem {
  id: string;
  name: string;
  price: number;
  image: ImageSourcePropType;
  quantity?: number;
  size?: string;
}

interface CartScreenProps {
  navigation: NativeStackNavigationProp<RootStackParamList>;
  orders: OrderItem[];
  updateOrderQuantity: (index: number, quantity: number) => void;
  removeOrder: (order: OrderItem) => void;
  clearOrders: () => void;
}

// Deklarasi global untuk objek 'snap' Midtrans (opsional, jika Anda menggunakan Snap JS di WebView)
// Perlu diingat bahwa Snap.js di WebView mungkin memiliki cara interaksi yang berbeda
// dengan React Native dibandingkan di web browser biasa.
// Kalau Anda menggunakan 'WebView' untuk menampilkan halaman Midtrans Snap,
// maka interaksi dengan window.snap.pay() akan terjadi di dalam WebView itu sendiri,
// bukan di konteks React Native utama. Callback-nya juga perlu ditangani di WebView.
// Untuk saat ini, kita asumsikan WebView menangani interaksi Snap sepenuhnya.
declare global {
  interface Window {
    snap: {
      pay: (
        token: string,
        callbacks: {
          onSuccess: (result: any) => void;
          onPending: (result: any) => void;
          onError: (result: any) => void;
          onClose: () => void;
        }
      ) => void;
    };
  }
}

class CartScreen extends Component<CartScreenProps> {
  state = {
    showPayment: false,
    snapToken: '',
    catatan: '',
    isLoading: false,
    paymentTimeout: false,
    retryCount: 0,
    maxRetries: 3,
  };

  private getTotalPrice = (): number => {
    return this.props.orders.reduce((total, item) =>
      total + (item.price * (item.quantity || 1)), 0);
  };

  private handleQuantityChange = (index: number, change: number): void => {
    const order = this.props.orders[index];
    const newQuantity = (order.quantity || 1) + change;
    if (newQuantity > 0) {
      this.props.updateOrderQuantity(index, newQuantity);
    } else {
      this.props.removeOrder(order);
    }
  };

  private handleCheckout = async (): Promise<void> => {
    // Jika sedang loading, jangan lakukan apa-apa
    if (this.state.isLoading) {
      return;
    }

    // Set isLoading menjadi true untuk menonaktifkan tombol
    this.setState({ isLoading: true });

    try {
      // Validasi keranjang
      if (!this.props.orders || this.props.orders.length === 0) {
        Alert.alert('Error', 'Keranjang belanja kosong');
        return;
      }

      // Validasi item per item
      for (const order of this.props.orders) {
        if (!order.id || !order.price || !order.quantity || order.quantity < 1) {
          Alert.alert('Error', 'Data item tidak valid');
          return;
        }
        
        // Validasi harga minimum
        if (order.price < 1000) {
          Alert.alert('Error', `Harga minimum untuk ${order.name} adalah Rp 1.000`);
          return;
        }
        
        // Validasi quantity maksimum
        if (order.quantity > 50) {
          Alert.alert('Error', `Maksimum 50 item untuk ${order.name}`);
          return;
        }
      }

      const items = this.props.orders.map(order => ({
        id: order.id,
        quantity: order.quantity || 1,
        size: order.size || 'sedang',
        price: order.price,
        name: order.name,
      }));

      const totalAmount = this.getTotalPrice();
      
      // Validasi total pembayaran
      if (totalAmount <= 0) {
        Alert.alert('Error', 'Total pembayaran tidak valid');
        return;
      }
      
      // Validasi minimum pembayaran
      if (totalAmount < 5000) {
        Alert.alert('Error', 'Minimum pembayaran adalah Rp 5.000');
        return;
      }
      
      // Validasi maksimum pembayaran
      if (totalAmount > 1000000) {
        Alert.alert('Error', 'Maksimum pembayaran adalah Rp 1.000.000');
        return;
      }

      // Validasi user data
      const userDataString = await AsyncStorage.getItem('user_data');
      const userData = userDataString ? JSON.parse(userDataString) : null;
      if (!userData || !userData.id) {
        Alert.alert('Error', 'Gagal mendapatkan data pengguna. Silakan login ulang.');
        return;
      }

      // Konfirmasi pembayaran
      const confirmPayment = await new Promise((resolve) => {
        Alert.alert(
          'Konfirmasi Pembayaran',
          `Total pembayaran: Rp ${totalAmount.toLocaleString('id-ID')}\n\nLanjutkan ke pembayaran?`,
          [
            { text: 'Batal', onPress: () => resolve(false), style: 'cancel' },
            { text: 'Lanjutkan', onPress: () => resolve(true) }
          ]
        );
      });

      if (!confirmPayment) {
        this.setState({ isLoading: false });
        return;
      }

      const requestBody = {
        items,
        total_amount: totalAmount,
        customer_details: {
          first_name: userData.nama || 'Customer',
          email: userData.email || 'customer@example.com',
          phone: userData.no_hp || '08123456789',
        },
        catatan: this.state.catatan,
        user_id: userData.id,
      };

      const response = await api.post('/payment/checkout', requestBody);
      const data = response.data;

      if (data.payment_status === 'sukses' && data.data?.snap_token) {
        this.setState({ showPayment: true, snapToken: data.data.snap_token });
      } else {
        Alert.alert('Error', data.pesan || 'Gagal mendapatkan token pembayaran.');
      }
    } catch (error: any) {
      console.error('Checkout Error:', error);
      let errorMessage = 'Terjadi kesalahan saat melakukan checkout.';
      if (error.response?.data?.pesan) {
        errorMessage = error.response.data.pesan;
      }
      Alert.alert('Error', errorMessage);
    } finally {
      // Selalu set isLoading kembali ke false setelah request selesai (sukses atau gagal)
      this.setState({ isLoading: false });
    }
  };

  private handlePaymentSuccess = () => {
    // Track successful payment
    this.trackPaymentEvent('payment_success', {
      total_amount: this.getTotalPrice(),
      items_count: this.props.orders.length,
      payment_method: 'midtrans'
    });

    // Fungsi ini dipanggil jika pembayaran di Midtrans berhasil.
    Alert.alert('Pembayaran Berhasil', 'Terima kasih atas pembelian Anda', [
      {
        text: 'OK',
        onPress: () => {
          this.setState({ showPayment: false });
          this.props.clearOrders();
          this.props.navigation.navigate('NotifikasiScreen', {
            newOrder: {
              items: this.props.orders.map(order => ({
                id: order.id,
                name: order.name,
                price: order.price.toString(),
                quantity: order.quantity || 1
              })),
              total: this.getTotalPrice(),
              status: 'sudah dibayar', // Status sudah dibayar karena pembayaran berhasil
              catatan: this.state.catatan // Tambahkan catatan ke notifikasi
            }
          });
        }
      }
    ]);
  };

  private trackPaymentEvent = (eventName: string, data: any) => {
    try {
      // Track payment events for analytics
      console.log(`Payment Event: ${eventName}`, data);
      
      // You can integrate with analytics services here
      // Example: Firebase Analytics, Mixpanel, etc.
      
      // For now, we'll just log to console
      const eventData = {
        event: eventName,
        timestamp: new Date().toISOString(),
        user_id: null, // Will be filled from AsyncStorage
        ...data
      };
      
      // Get user data for tracking
      AsyncStorage.getItem('user_data').then(userDataString => {
        if (userDataString) {
          const userData = JSON.parse(userDataString);
          eventData.user_id = userData.id;
        }
        console.log('Analytics Event:', eventData);
      });
      
    } catch (error) {
      console.error('Error tracking payment event:', error);
    }
  };

  private handleGoBack = (): void => {
    this.props.navigation.goBack();
  };

  private handlePaymentTimeout = () => {
    this.setState({ paymentTimeout: true });
    Alert.alert(
      'Timeout Pembayaran',
      'Pembayaran memakan waktu terlalu lama. Silakan coba lagi.',
      [
        {
          text: 'Coba Lagi',
          onPress: () => {
            this.setState({ paymentTimeout: false, retryCount: this.state.retryCount + 1 });
            if (this.state.retryCount < this.state.maxRetries) {
              this.handleCheckout();
            } else {
              Alert.alert('Error', 'Terlalu banyak percobaan. Silakan coba lagi nanti.');
              this.setState({ retryCount: 0 });
            }
          }
        },
        {
          text: 'Batal',
          onPress: () => {
            this.setState({ 
              showPayment: false, 
              snapToken: '', 
              paymentTimeout: false, 
              retryCount: 0 
            });
          },
          style: 'cancel'
        }
      ]
    );
  };

  private handlePaymentRetry = () => {
    if (this.state.retryCount < this.state.maxRetries) {
      this.setState({ retryCount: this.state.retryCount + 1 });
      this.handleCheckout();
    } else {
      Alert.alert('Error', 'Terlalu banyak percobaan. Silakan coba lagi nanti.');
      this.setState({ retryCount: 0 });
    }
  };

  public render(): JSX.Element {
    const { orders } = this.props;
    const { showPayment, snapToken, isLoading } = this.state; // Ambil isLoading dari state

    // Logic untuk WebView tetap sama
    if (showPayment && snapToken) {
      const snapUrl = `https://app.sandbox.midtrans.com/snap/v2/vtweb/${snapToken}`;
      return (
        <SafeAreaView style={styles.container}>
          <WebView
            source={{ uri: snapUrl }}
            style={{ flex: 1 }}
            injectedJavaScript={`
              // Inject JavaScript untuk menangani callback Midtrans
              window.snap.pay('${snapToken}', {
                onSuccess: function(result){
                  window.ReactNativeWebView.postMessage(JSON.stringify({ type: 'success', data: result }));
                },
                onPending: function(result){
                  window.ReactNativeWebView.postMessage(JSON.stringify({ type: 'pending', data: result }));
                },
                onError: function(result){
                  window.ReactNativeWebView.postMessage(JSON.stringify({ type: 'error', data: result }));
                },
                onClose: function(){
                  window.ReactNativeWebView.postMessage(JSON.stringify({ type: 'close' }));
                }
              });
              true; // return true to ensure the JS is evaluated
            `}
            onMessage={(event) => {
              try {
                const message = JSON.parse(event.nativeEvent.data);
                console.log('WebView message:', message);
                
                switch (message.type) {
                  case 'success':
                    this.trackPaymentEvent('payment_success', {
                      total_amount: this.getTotalPrice(),
                      items_count: this.props.orders.length,
                      payment_method: 'midtrans'
                    });
                    this.handlePaymentSuccess();
                    break;
                  case 'pending':
                    this.trackPaymentEvent('payment_pending', {
                      total_amount: this.getTotalPrice(),
                      items_count: this.props.orders.length,
                      payment_method: 'midtrans'
                    });
                    Alert.alert('Pembayaran Pending', 'Pembayaran Anda sedang diproses. Silakan cek status pembayaran di notifikasi.');
                    this.setState({ showPayment: false });
                    break;
                  case 'error':
                    this.trackPaymentEvent('payment_error', {
                      total_amount: this.getTotalPrice(),
                      items_count: this.props.orders.length,
                      payment_method: 'midtrans',
                      error_message: message.data?.message || 'Unknown error'
                    });
                    Alert.alert(
                      'Pembayaran Gagal', 
                      'Terjadi kesalahan dalam pembayaran. Silakan coba lagi.',
                      [
                        { text: 'Batal', style: 'cancel' },
                        { text: 'Coba Lagi', onPress: () => this.handlePaymentRetry() }
                      ]
                    );
                    this.setState({ showPayment: false });
                    break;
                  case 'close':
                    this.trackPaymentEvent('payment_cancelled', {
                      total_amount: this.getTotalPrice(),
                      items_count: this.props.orders.length,
                      payment_method: 'midtrans'
                    });
                    Alert.alert(
                      'Pembayaran Dibatalkan', 
                      'Pembayaran dibatalkan oleh pengguna.',
                      [
                        { text: 'OK', style: 'cancel' },
                        { text: 'Coba Lagi', onPress: () => this.handlePaymentRetry() }
                      ]
                    );
                    this.setState({ showPayment: false });
                    break;
                }
              } catch (error: any) {
                console.error('Error parsing WebView message:', error);
                this.trackPaymentEvent('payment_parse_error', {
                  error_message: error?.message || 'Unknown parsing error',
                  total_amount: this.getTotalPrice()
                });
              }
            }}
            onNavigationStateChange={(navState) => {
              // Backup detection untuk URL-based success
              if (navState.url.includes('midtrans.com/success') || 
                  navState.url.includes('midtrans.com/finish') ||
                  navState.url.includes('status=success')) {
                this.handlePaymentSuccess();
              }
            }}
            onLoadStart={(syntheticEvent) => {
              const { nativeEvent } = syntheticEvent;
              console.log('WebView Load Started:', nativeEvent.url);
            }}
            onLoadEnd={(syntheticEvent) => {
              const { nativeEvent } = syntheticEvent;
              console.log('WebView Load Ended:', nativeEvent.url);
            }}
            onError={(syntheticEvent) => {
              const { nativeEvent } = syntheticEvent;
              console.error('WebView Error:', nativeEvent.description, nativeEvent.url);
              Alert.alert('Error Memuat Pembayaran', 'Terjadi masalah saat memuat halaman pembayaran. Coba lagi atau periksa koneksi internet Anda.', [
                {
                  text: 'OK',
                  onPress: () => this.setState({ showPayment: false, snapToken: '' }) // Kembali ke CartScreen
                }
              ]);
            }}
          />
        </SafeAreaView>
      );
    }

    return (
      <SafeAreaView style={styles.container}>
        <View style={styles.header}>
          <TouchableOpacity onPress={this.handleGoBack} style={styles.backButton}>
            <Ionicons name="chevron-back" size={24} color="#FF8C00" />
            <Text style={{ color: '#FF8C00', fontWeight: 'bold', marginLeft: 4 }}>Kembali</Text>
          </TouchableOpacity>
          <Text style={styles.headerTitle}>KERANJANG</Text>
          <View style={{ width: 24 }} />
        </View>

        <ScrollView style={styles.cartList}>
          {orders.length > 0 ? (
            orders.map((item, index) => (
              <View key={`${item.id}-${item.size}-${index}`} style={styles.cartItem}>
                <Image source={item.image} style={styles.itemImage} />
                <View style={styles.itemInfo}>
                  <Text style={styles.itemName}>{item.name}</Text>
                  <Text style={styles.itemPrice}>Rp{item.price.toLocaleString()}</Text>
                  <View style={styles.quantityContainer}>
                    <TouchableOpacity onPress={() => this.handleQuantityChange(index, -1)} style={styles.quantityButton}>
                      <Ionicons name="remove" size={20} color="#FF8C00" />
                    </TouchableOpacity>
                    <Text style={styles.quantityText}>{item.quantity || 1}</Text>
                    <TouchableOpacity onPress={() => this.handleQuantityChange(index, 1)} style={styles.quantityButton}>
                      <Ionicons name="add" size={20} color="#FF8C00" />
                    </TouchableOpacity>
                  </View>
                </View>
                <Text style={styles.itemTotalPrice}>Rp{((item.quantity || 1) * item.price).toLocaleString()}</Text>
              </View>
            ))
          ) : (
            <View style={styles.emptyCart}>
              <Text style={styles.emptyCartText}>Keranjang belanja kosong</Text>
            </View>
          )}
        </ScrollView>

        {orders.length > 0 && (
          <View style={styles.footer}>
            <View style={styles.totalContainer}>
              <Text style={styles.totalLabel}>TOTAL</Text>
              <Text style={styles.totalPrice}>Rp{this.getTotalPrice().toLocaleString()}</Text>
            </View>
            <View style={styles.catatanContainer}>
              <Text style={styles.catatanLabel}>Catatan:</Text>
              <TextInput
                style={styles.catatanInput}
                placeholder="Tambahkan catatan untuk pesanan Anda"
                value={this.state.catatan}
                onChangeText={(text) => this.setState({ catatan: text })}
                multiline
              />
            </View>
            
            {/* Informasi metode pembayaran */}
            <View style={styles.paymentInfoContainer}>
              <Text style={styles.paymentInfoTitle}>Metode Pembayaran:</Text>
              <View style={styles.paymentMethods}>
                <View style={styles.paymentMethod}>
                  <Ionicons name="card" size={20} color="#4CAF50" />
                  <Text style={styles.paymentMethodText}>E-Wallet (GoPay, ShopeePay)</Text>
                </View>
                <View style={styles.paymentMethod}>
                  <Ionicons name="business" size={20} color="#2196F3" />
                  <Text style={styles.paymentMethodText}>Transfer Bank</Text>
                </View>
              </View>
            </View>

            {/* PERUBAHAN DI SINI: Nonaktifkan tombol saat isLoading*/}
            <TouchableOpacity
              style={[styles.checkoutButton, isLoading && styles.checkoutButtonDisabled]}
              onPress={this.handleCheckout}
              disabled={isLoading} // Tombol dinonaktifkan saat isLoading
            >
              {isLoading ? (
                <View style={styles.loadingContainer}>
                  <ActivityIndicator color="white" size="small" />
                  <Text style={styles.loadingText}>Memproses...</Text>
                </View>
              ) : (
                <Text style={styles.checkoutButtonText}>Lanjutkan Order</Text>
              )}
            </TouchableOpacity>
          </View>
        )}
      </SafeAreaView>
    );
  }
}

// Tidak ada perubahan pada CartScreenWithNavigation karena ini hanya wrapper fungsional
const CartScreenWithNavigation = () => {
  const navigation = useNavigation<NativeStackNavigationProp<RootStackParamList>>();
  const { orders, updateOrderQuantity, removeOrder, clearOrders } = useOrder();

  return (
    <CartScreen
      navigation={navigation}
      orders={orders}
      updateOrderQuantity={updateOrderQuantity}
      removeOrder={removeOrder}
      clearOrders={clearOrders}
    />
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: 16,
    backgroundColor: 'white',
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
  },
  headerTitle: {
    fontSize: 16,
    fontWeight: 'bold',
  },
  cartList: {
    flex: 1,
  },
  cartItem: {
    flexDirection: 'row',
    backgroundColor: 'white',
    padding: 15,
    marginVertical: 4,
    marginHorizontal: 8,
    borderRadius: 15,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 2,
    },
    shadowOpacity: 0.1,
    shadowRadius: 3.84,
    elevation: 3,
  },
  itemImage: {
    width: 70,
    height: 70,
    borderRadius: 35,
    backgroundColor: '#f5f5f5',
    resizeMode: 'cover'
  },
  itemInfo: {
    flex: 1,
    marginLeft: 12,
  },
  itemName: {
    fontSize: 16,
    fontWeight: '600',
  },
  itemPrice: {
    fontSize: 14,
    fontWeight: '600',
    color: '#FF8C00',
    marginTop: 2,
    marginBottom: 8,
  },
  quantityContainer: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  quantityButton: {
    width: 30,
    height: 30,
    borderRadius: 15,
    backgroundColor: '#FFF5E6',
    justifyContent: 'center',
    alignItems: 'center',
  },
  quantityText: {
    fontSize: 16,
    fontWeight: '500',
    marginHorizontal: 12,
  },
  itemTotalPrice: {
    fontSize: 14,
    fontWeight: '600',
    color: '#FF8C00',
    marginLeft: 8,
  },
  footer: {
    backgroundColor: 'white',
    padding: 16,
    borderTopWidth: 1,
    borderTopColor: '#eee',
  },
  totalContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 16,
  },
  totalLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: '#666',
  },
  totalPrice: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#FF8C00',
  },
  checkoutButton: {
    backgroundColor: '#FF8C00',
    padding: 16,
    borderRadius: 8,
    alignItems: 'center',
  },
  // Style baru untuk tombol yang dinonaktifkan
  checkoutButtonDisabled: {
    backgroundColor: '#FFBE7D', // Warna sedikit lebih terang saat dinonaktifkan
  },
  checkoutButtonText: {
    color: 'white',
    fontSize: 16,
    fontWeight: 'bold',
  },
  emptyCart: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  emptyCartText: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#666',
  },
  catatanContainer: {
    marginBottom: 16,
  },
  catatanLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: '#666',
    marginBottom: 8,
  },
  catatanInput: {
    backgroundColor: '#f5f5f5',
    borderRadius: 8,
    padding: 12,
    fontSize: 14,
    color: '#333',
    textAlignVertical: 'top',
    minHeight: 80,
    borderWidth: 1,
    borderColor: '#ddd',
  },
  backButton: {
    width: 'auto',
    height: 40,
    justifyContent: 'center',
    alignItems: 'center',
    flexDirection: 'row',
    paddingHorizontal: 8,
  },
  paymentInfoContainer: {
    marginBottom: 16,
  },
  paymentInfoTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: '#666',
    marginBottom: 8,
  },
  paymentMethods: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  paymentMethod: {
    flexDirection: 'row',
    alignItems: 'center',
    marginRight: 16,
  },
  paymentMethodText: {
    fontSize: 14,
    fontWeight: '500',
    color: '#333',
    marginLeft: 8,
  },
  loadingContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
  },
  loadingText: {
    fontSize: 14,
    fontWeight: 'bold',
    color: 'white',
    marginLeft: 8,
  },
});

export default CartScreenWithNavigation;