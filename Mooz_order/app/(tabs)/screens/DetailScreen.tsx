import React, { useState } from 'react';
import { 
  View, 
  Text, 
  SafeAreaView, 
  StyleSheet, 
  Image, 
  TouchableOpacity, 
  Alert 
} from 'react-native'; 
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { RootStackParamList } from '../navigation/AppNavigator';
import { Ionicons } from '@expo/vector-icons';
import { useOrder } from '../context/OrderContext'; 

type DetailScreenProps = NativeStackScreenProps<RootStackParamList, 'DetailScreen'>;

function formatRupiah(value: string | number): string {
  const number = typeof value === 'string' ? parseInt(value, 10) : value;
  return 'Rp ' + number.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 }); 
}

export default function DetailScreen({ route, navigation }: DetailScreenProps) {
  const { item } = route.params;
  const [isFavorite, setIsFavorite] = useState(false); 
  const { addOrder, orders, removeOrder } = useOrder();

  const handleAddToCart = () => {
    let itemId = '';
    if (item.id !== null && item.id !== undefined) {
        itemId = String(item.id);
    } else {
        console.error('Item ID is null or undefined:', item.id);
        Alert.alert('Error', 'ID produk tidak valid. Tidak dapat menambahkan ke keranjang.');
        return;
    }
    
    const price = typeof item.price === 'string' ? parseFloat(item.price) : item.price;

    const existingOrder = orders.find(order => String(order.id) === itemId && order.size === item.size);

    if (existingOrder) {
      Alert.alert(
        "Info",
        "Produk ini sudah ada di keranjang Anda. Kuantitas akan ditambahkan.",
        [ { text: "OK" } ]
      );
    }

    const itemToAdd = {
      id: itemId,
      name: item.name,
      price: price,
      image: item.image,
      size: item.size, 
      quantity: 1
    };

    addOrder(itemToAdd);
    Alert.alert(
      "Berhasil",
      "Produk telah ditambahkan ke keranjang",
      [
        { text: "Lanjut Belanja", onPress: () => {} },
        { text: "Lihat Keranjang", onPress: () => navigation.navigate('CartScreen') }
      ]
    );
  };

  const handleRemoveFromCart = () => {
    const itemId = String(item.id); 
    const itemSize = item.size;

    const orderToRemove = orders.find(order => String(order.id) === itemId && order.size === itemSize);

    if (orderToRemove) {
      removeOrder({ 
        id: itemId, 
        size: itemSize 
      });
      Alert.alert("Dihapus", "Item telah dihapus dari keranjang");
    } else {
      Alert.alert("Info", "Produk tidak ada di keranjang Anda.");
    }
  };

  return (
    <SafeAreaView style={styles.container}>
      {/* Header */}
      <View style={styles.header}>
        <TouchableOpacity onPress={() => navigation.goBack()} style={styles.headerIcon}>
          <Ionicons name="chevron-back" size={26} color="#333" />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Detail Produk</Text>
        <View style={styles.headerIconPlaceholder} />
      </View>

      {/* Product Image Wrapper - full width, fixed height */}
      <View style={styles.productImageWrapper}>
        <Image 
          source={item.image}
          style={styles.productImage}
          resizeMode="cover" // 'cover' untuk memotong dan mengisi penuh
        />
      </View>

      {/* Content Container - mengambil sisa ruang flex:1, padding lebih ringkas */}
      <View style={styles.contentContainer}>
        <Text style={styles.title}>{item.name}</Text>
        <Text style={styles.subtitle}>{item.description}</Text> 
        
        {/* Deskripsi Produk */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Deskripsi Produk</Text> 
          <Text style={styles.description}>
            {item.description}
          </Text>
        </View>

        {/* Ukuran Produk */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Ukuran</Text>
          <View style={styles.sizeOption}>
            <Text style={styles.sizeDisplayText}>
              {item.size ? item.size : 'Ukuran tidak tersedia'}
            </Text>
          </View>
        </View>
      </View>

      {/* Footer - selalu di bawah */}
      <View style={styles.footer}>
        <View style={styles.footerContent}>
          <View style={styles.priceDisplayContainer}>
            <Text style={styles.priceLabel}>Total Harga</Text> 
            <Text style={styles.price}>{formatRupiah(item.price)}</Text>
          </View>
          <View style={styles.buttonContainer}>
            <TouchableOpacity 
              style={styles.addToCartButton} 
              activeOpacity={0.7}
              onPress={handleAddToCart}
            >
              <Text style={styles.addToCartButtonText}>Tambahkan ke Keranjang</Text>
            </TouchableOpacity>
          </View>
        </View>
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F8F8F8',
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 18,
    paddingHorizontal: 20,
    paddingTop: 50,
    backgroundColor: '#FFFFFF',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 5,
  },
  headerIcon: {
    padding: 5,
  },
  headerIconPlaceholder: {
    width: 26 + (5 * 2),
    height: 26 + (5 * 2),
  },
  headerTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#333',
  },
  productImageWrapper: {
    backgroundColor: '#FFFFFF',
    height: 250,
    alignItems: 'center',
    justifyContent: 'center',
    // Tidak ada padding/margin horizontal di wrapper agar gambar full width
    overflow: 'hidden', // Penting untuk memastikan gambar 'cover' tidak keluar
    // Menambahkan bayangan di bawah gambar
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.1,
    shadowRadius: 6,
    elevation: 6,
    marginBottom: 20, // Jarak dari gambar ke konten di bawahnya
  },
  productImage: {
    width: '100%',
    height: '100%', // Mengisi penuh wrapper
    resizeMode: 'cover', // Memotong dan mengisi penuh
  },
  contentContainer: {
    flex: 1,
    paddingHorizontal: 20, // Padding horizontal untuk konten
    // paddingBottom: 0, // Hapus padding bawah jika tidak digunakan
  },
  title: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 8,
  },
  subtitle: {
    fontSize: 16,
    color: '#666',
    marginBottom: 15,
  },
  section: {
    marginBottom: 20, // Jarak antar bagian
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 10,
  },
  description: {
    fontSize: 15,
    color: '#555',
    lineHeight: 22,
  },
  sizeOption: {
    backgroundColor: '#E8E8E8',
    paddingVertical: 8,
    paddingHorizontal: 15,
    borderRadius: 8,
    alignSelf: 'flex-start',
  },
  sizeDisplayText: {
    fontSize: 15,
    color: '#333',
    fontWeight: '600',
  },
  footer: {
    backgroundColor: '#FFFFFF',
    padding: 20,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: -4 },
    shadowOpacity: 0.1,
    shadowRadius: 6,
    elevation: 10,
  },
  footerContent: { // *** KUNCI: Container baru untuk harga & tombol ***
    flexDirection: 'column', // Susun secara vertikal
    alignItems: 'flex-start', // Rata kiri untuk seluruh konten footer
  },
  priceDisplayContainer: { // Wrapper untuk teks harga agar bisa diatur margin bawah
    marginBottom: 15, // Jarak antara harga dan tombol
  },
  priceLabel: {
    fontSize: 14,
    color: '#666',
    marginBottom: 5,
  },
  price: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#FF8C00',
  },
  buttonContainer: {
    flexDirection: 'row',
    gap: 15,
    width: '100%', // Pastikan container tombol mengambil lebar penuh footer
    justifyContent: 'flex-end', // Tombol tetap rata kanan di dalam container tombol
  },
  addToCartButton: {
    backgroundColor: 'transparent',
    borderWidth: 1.5,
    borderColor: '#FF8C00',
    paddingHorizontal: 15,
    paddingVertical: 12,
    borderRadius: 12,
    justifyContent: 'center',
    alignItems: 'center',
    flex: 1,
    maxWidth: '50%',
  },
  addToCartButtonText: {
    color: '#FF8C00',
    fontWeight: 'bold',
    fontSize: 15,
  },
});