import React, { Component } from 'react';
import { View, Text, TextInput, StyleSheet, Image, ScrollView, TouchableOpacity, SafeAreaView, Alert, ActivityIndicator, Animated } from 'react-native';
import { MaterialIcons, Ionicons } from '@expo/vector-icons';
import { useNavigation, RouteProp, useRoute } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { RootStackParamList } from '../navigation/AppNavigator';
import { useOrder } from '../context/OrderContext';
import { productAPI } from '../../services/api';
import AsyncStorage from '@react-native-async-storage/async-storage';

// Define the route parameter type for HomeScreen
type HomeScreenRouteProp = RouteProp<RootStackParamList, 'HomeScreen'>;

interface Product {
    id: number;
    id_produk: string;
    nama_produk: string;
    harga_produk: number;
    foto_produk: string; // This is a JSON string of array of photos
    keterangan_produk: string;
    kategori_produk: string;
    ukuran_produk: string;
    nama: string;
}

interface HomeScreenProps {
    navigation: NativeStackNavigationProp<RootStackParamList>;
    route: HomeScreenRouteProp;
    addOrder: (order: any) => void;
}

interface HomeScreenState {
    isBottomBarVisible: boolean;
    selectedCategory: string;
    searchQuery: string;
    products: Product[];
    loading: boolean;
    error: string | null;
    isFilterMenuVisible: boolean;
    isCategoryFilterActive: boolean;
    filterButtonScale: Animated.Value; // For animation
    userName?: string; // Tambahan untuk nama user
}

function formatRupiah(value: string | number): string {
    const number = typeof value === 'string' ? parseInt(value, 10) : value;
    return 'Rp ' + number.toLocaleString('id-ID').replace(/,/g, '.');
}

class HomeScreen extends Component<HomeScreenProps, HomeScreenState> {
    private scrollViewRef: React.RefObject<ScrollView>;

    constructor(props: HomeScreenProps) {
        super(props);
        this.state = {
            isBottomBarVisible: true,
            selectedCategory: 'All', // Default to 'All'
            searchQuery: '',
            products: [],
            loading: true,
            error: null,
            isFilterMenuVisible: false,
            isCategoryFilterActive: true, // Set default isCategoryFilterActive to TRUE so categories are visible by default
            filterButtonScale: new Animated.Value(1), // Initialize Animated.Value
        };
        this.scrollViewRef = React.createRef();
    }

    async componentDidMount() {
        // Ambil nama user dari AsyncStorage
        try {
            const userDataStr = await AsyncStorage.getItem('user_data');
            let userName = undefined;
            if (userDataStr) {
                const userData = JSON.parse(userDataStr);
                userName = userData?.nama;
            }
            this.setState({ userName });
        } catch (e) {
            this.setState({ userName: undefined });
        }
        await this.fetchProducts();
    }

    private fetchProducts = async () => {
        try {
            this.setState({ loading: true, error: null });
            const response = await productAPI.getAll();
            this.setState({ products: response.data, loading: false });
        } catch (error) {
            this.setState({
                error: 'Failed to fetch products',
                loading: false
            });
            Alert.alert('Error', 'Failed to load products');
        }
    };

    private handleCategoryChange = (category: string): void => {
        this.setState({ selectedCategory: category }, () => {
            this.scrollViewRef.current?.scrollTo({ y: 0, animated: true });
        });
    };

    private toggleCategoryFilter = (): void => {
        // Animation for the filter button
        Animated.sequence([
            Animated.timing(this.state.filterButtonScale, {
                toValue: 0.9,
                duration: 100,
                useNativeDriver: true,
            }),
            Animated.timing(this.state.filterButtonScale, {
                toValue: 1,
                duration: 100,
                useNativeDriver: true,
            }),
        ]).start();

        this.setState(prevState => ({
            isCategoryFilterActive: !prevState.isCategoryFilterActive,
            // Jika filter dinonaktifkan, reset kategori ke 'All'
            selectedCategory: !prevState.isCategoryFilterActive ? prevState.selectedCategory : 'All'
        }));
    };

    private handleSearch = (query: string): void => {
        this.setState({ searchQuery: query });
    };

    private handleProductPress = (product: Product): void => {
        let imageUrl = '';
        try {
            const fotos = JSON.parse(product.foto_produk);
            if (Array.isArray(fotos) && fotos.length > 0 && fotos[0].foto_base64) {
                imageUrl = fotos[0].foto_base64.startsWith('data:image/')
                    ? fotos[0].foto_base64
                    : `data:image/${fotos[0].tipe || 'jpeg'};base64,${fotos[0].foto_base64}`;
            }
        } catch (e) {
            console.error("Failed to parse foto_produk or get base64:", e);
            imageUrl = 'https://via.placeholder.com/100'; // Fallback
        }

        this.props.navigation.navigate('DetailScreen', {
            item: {
                id: product.id_produk,
                name: product.nama_produk,
                price: product.harga_produk.toString(),
                description: product.keterangan_produk,
                image: { uri: imageUrl },
                size: product.ukuran_produk,
            }
        });
    };

    private getCategories = (): string[] => {
        if (!Array.isArray(this.state.products)) {
            console.warn('Products state is not an array in getCategories:', this.state.products);
            return [];
        }
        const categories = new Set(this.state.products.map(p => p.kategori_produk));
        const allCategories = ['All', ...Array.from(categories)]; // Tambahkan 'All'
        return allCategories;
    };

    private getFilteredProducts = (): Product[] => {
        if (!Array.isArray(this.state.products)) {
            console.warn('Products state is not an array:', this.state.products);
            return [];
        }

        let filtered = this.state.products;

        if (this.state.isCategoryFilterActive && this.state.selectedCategory !== 'All') {
            filtered = filtered.filter(product =>
                product.kategori_produk === this.state.selectedCategory
            );
        }

        if (this.state.searchQuery) {
            filtered = filtered.filter(product =>
                product.nama_produk.toLowerCase().includes(this.state.searchQuery.toLowerCase())
            );
        }
        return filtered;
    };

    private confirmLogout = (): void => {
        Alert.alert(
            'Konfirmasi Logout',
            'Apakah Anda yakin ingin logout?',
            [
                {
                    text: 'Tidak',
                    onPress: () => console.log('Logout dibatalkan'),
                    style: 'cancel',
                },
                {
                    text: 'Ya',
                    onPress: this.handleLogout,
                },
            ],
            { cancelable: false }
        );
    };

    private handleLogout = async (): Promise<void> => {
        try {
            await AsyncStorage.removeItem('token');
            this.props.navigation.reset({
                index: 0,
                routes: [{ name: 'Login' }],
            });
        } catch (error) {
            console.error('Error logging out:', error);
            Alert.alert('Error', 'Gagal logout.');
        }
    };

    private renderHeader(): JSX.Element {
        const userName = this.state.userName || 'Pelanggan';
        return (
            <View style={styles.headerNew}>
                <Text style={styles.headerTitle}>{userName}</Text>
                <TouchableOpacity onPress={this.confirmLogout}>
                    <MaterialIcons name="logout" size={28} color="#FF8C00" />
                </TouchableOpacity>
            </View>
        );
    }

    private renderSearchBar(): JSX.Element {
        const { isCategoryFilterActive, filterButtonScale } = this.state;
        const filterButtonBackgroundColor = isCategoryFilterActive ? '#FF8C00' : '#F0F0F0';
        return (
            <View style={styles.searchContainerNew}>
                <Ionicons name="search" size={20} color="#888" style={styles.searchIconNew} />
                <TextInput
                    style={styles.searchInputNew}
                    placeholder="Search Favorite Food"
                    placeholderTextColor="#888"
                    value={this.state.searchQuery}
                    onChangeText={this.handleSearch}
                />
            </View>
        );
    }

    private renderFilterMenu(): JSX.Element | null {
        if (!this.state.isCategoryFilterActive) {
            return null;
        }
        // Ambil kategori dari data produk
        const categories = this.getCategories();
        // Mapping emoji kategori
        const emojiMap: Record<string, string> = {
            'All': '🍽️',
            'Bakery': '🍞',
            'Seafood': '🦐',
            'Pizza': '🍕',
            // Tambahkan mapping lain jika perlu
        };
        return (
            <View style={styles.categoryRowNew}>
                {categories.map((cat) => (
                <TouchableOpacity
                        key={cat}
                        style={[styles.categoryPill, this.state.selectedCategory === cat && { backgroundColor: '#2ECC71' }]}
                        onPress={() => this.handleCategoryChange(cat)}
                    >
                        <Text style={[styles.categoryPillText, this.state.selectedCategory === cat && { color: '#fff' }]}> 
                            {emojiMap[cat] || '🍽️'} {cat === 'All' ? 'All Food' : cat}
                        </Text>
                </TouchableOpacity>
                ))}
            </View>
        );
    }

    private renderProducts(): JSX.Element[] | JSX.Element {
        if (this.state.loading) {
            return (
                <View style={styles.statusContainer}>
                    <ActivityIndicator size="large" color="#FF8C00" />
                    <Text style={styles.statusText}>Memuat produk...</Text>
                </View>
            );
        }
        if (this.state.error) {
            return (
                <View style={styles.statusContainer}>
                    <Ionicons name="alert-circle-outline" size={50} color="#D32F2F" />
                    <Text style={styles.statusText}>{this.state.error}</Text>
                    <TouchableOpacity onPress={this.fetchProducts} style={styles.retryButton}>
                        <Text style={styles.retryButtonText}>Coba Lagi</Text>
                    </TouchableOpacity>
                </View>
            );
        }
        const filteredProducts = this.getFilteredProducts();
        if (filteredProducts.length === 0) {
            return (
                <View style={styles.statusContainer}>
                    <Ionicons name="sad-outline" size={50} color="#FF8C00" />
                    <Text style={styles.statusText}>Produk tidak ditemukan.</Text>
                    <Text style={styles.statusSubText}>Coba kategori lain atau sesuaikan pencarian Anda.</Text>
                </View>
            );
        }
        return (
            <View style={styles.productGrid}>
                {filteredProducts.map((product) => {
            let imageUrl = '';
            try {
                const fotos = JSON.parse(product.foto_produk);
                if (Array.isArray(fotos) && fotos.length > 0 && fotos[0].foto_base64) {
                    imageUrl = fotos[0].foto_base64.startsWith('data:image/')
                        ? fotos[0].foto_base64
                        : `data:image/${fotos[0].tipe || 'jpeg'};base64,${fotos[0].foto_base64}`;
                }
            } catch (e) {
                        imageUrl = 'https://via.placeholder.com/100';
            }
            return (
                <TouchableOpacity
                    key={product.id_produk}
                            style={styles.productCardNew}
                    activeOpacity={0.7}
                    onPress={() => this.handleProductPress(product)}
                >
                            <View style={styles.productImageWrapper}>
                                <Image source={{ uri: imageUrl }} style={styles.productImageNew} />
                            </View>
                            <Text style={styles.productTitleNew}>{product.nama_produk}</Text>
                            <View style={styles.productRowInfo}>
                                <Text style={styles.productInfoText}>{product.ukuran_produk ? product.ukuran_produk : '-'}</Text>
                    </View>
                            <Text style={styles.productPriceNew}>{formatRupiah(product.harga_produk)}</Text>
                </TouchableOpacity>
            );
                })}
            </View>
        );
    }

    private renderBottomNav(): JSX.Element {
        return (
            <View style={styles.bottomNav}>
                <View style={styles.navContent}>
                    <TouchableOpacity style={styles.navItem} onPress={() => this.props.navigation.navigate({ name: 'CartScreen', params: undefined })}>
                        <Ionicons name="cart" size={26} color="#FF8C00" />
                        <Text style={styles.navText}>Keranjang</Text>
                    </TouchableOpacity>
                    <TouchableOpacity style={styles.navItem} onPress={() => this.props.navigation.navigate({ name: 'NotifikasiScreen', params: {} })}>
                        <Ionicons name="notifications" size={26} color="#FF8C00" />
                        <Text style={styles.navText}>Notifikasi</Text>
                    </TouchableOpacity>
                    <TouchableOpacity style={styles.navItem} onPress={() => this.props.navigation.navigate({ name: 'RiwayatScreen', params: undefined })}>
                        <Ionicons name="receipt" size={26} color="#FF8C00" />
                        <Text style={styles.navText}>Riwayat</Text>
                    </TouchableOpacity>
                    <TouchableOpacity style={styles.navItem} onPress={() => this.props.navigation.navigate({ name: 'ProfileScreen', params: undefined })}>
                        <Ionicons name="person" size={26} color="#FF8C00" />
                        <Text style={styles.navText}>Profil</Text>
                    </TouchableOpacity>
                </View>
            </View>
        );
    }

    public render(): JSX.Element {
        return (
            <SafeAreaView style={styles.container}>
                {this.renderHeader()}
                {this.renderSearchBar()}
                {this.renderFilterMenu()}

                <ScrollView
                    ref={this.scrollViewRef}
                    style={styles.scrollView}
                    contentContainerStyle={styles.productScrollContent}
                    showsVerticalScrollIndicator={false}
                >
                    {this.renderProducts()}
                </ScrollView>
                {this.renderBottomNav()}
            </SafeAreaView>
        );
    }
}

const HomeScreenWithNavigation = () => {
    const navigation = useNavigation<NativeStackNavigationProp<RootStackParamList>>();
    const route = useRoute<HomeScreenRouteProp>();
    const { addOrder } = useOrder();
    return <HomeScreen navigation={navigation} route={route} addOrder={addOrder} />;
};

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#F8F8F8',
    },
    scrollView: {
        flex: 1,
    },
    productScrollContent: {
        paddingHorizontal: 15,
        paddingTop: 10,
        paddingBottom: 100,
    },
    header: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        paddingVertical: 18,
        paddingHorizontal: 20,
        paddingTop: 40,
        backgroundColor: '#FF8C00',
        elevation: 8,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.25,
        shadowRadius: 5,
    },
    profileContainer: {
        flexDirection: 'row',
        alignItems: 'center',
    },
    profileImageContainer: {
        width: 48,
        height: 48,
        borderRadius: 24,
        marginRight: 12,
        justifyContent: 'center',
        alignItems: 'center',
        backgroundColor: 'rgba(255, 255, 255, 0.2)',
    },
    userText: {
        fontSize: 19,
        color: 'white',
        fontWeight: '700',
    },
    headerRightIcons: {
        flexDirection: 'row',
        alignItems: 'center',
    },
    iconMargin: {
        marginRight: 18,
    },
    searchContainer: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#FFFFFF',
        marginHorizontal: 15,
        marginVertical: 15,
        borderRadius: 12,
        paddingHorizontal: 15,
        elevation: 4,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.1,
        shadowRadius: 4,
        borderWidth: 1,
        borderColor: '#E0E0E0',
    },
    searchIcon: {
        marginRight: 10,
    },
    searchInput: {
        flex: 1,
        color: '#333',
        paddingVertical: 12,
        fontSize: 16,
    },
    filterToggleButton: {
        width: 36,
        height: 36,
        borderRadius: 8, 
        justifyContent: 'center',
        alignItems: 'center',
        marginLeft: 10,
        backgroundColor: 'transparent',
        overflow: 'hidden',
    },
    filterButtonInner: {
        width: '100%',
        height: '100%',
        borderRadius: 8,
        justifyContent: 'center',
        alignItems: 'center',
    },
    categoryScrollContainer: {
        paddingHorizontal: 15,
        paddingVertical: 10,
    },
    categoryButton: {
        paddingVertical: 10,
        paddingHorizontal: 20,
        borderRadius: 30,
        marginRight: 12,
        backgroundColor: '#FFFFFF',
        borderWidth: 1,
        borderColor: '#EFEFEF',
        justifyContent: 'center',
        alignItems: 'center',
        elevation: 2,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 1 },
        shadowOpacity: 0.05,
        shadowRadius: 2,
    },
    activeCategoryButton: {
        backgroundColor: '#FF8C00',
        borderColor: '#FF8C00',
        elevation: 4,
        shadowOpacity: 0.2,
    },
    categoryText: {
        fontSize: 12,
        color: '#333',
        fontWeight: '600',
    },
    activeCategoryText: {
        color: 'white',
        fontWeight: '500',
    },
    productCard: {
        backgroundColor: '#FFFFFF',
        padding: 15,
        borderRadius: 15,
        marginBottom: 15,
        flexDirection: 'row',
        alignItems: 'center',
        elevation: 5,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.15,
        shadowRadius: 5,
        borderWidth: 1,
        borderColor: '#EFEFEF',
    },
    productImage: {
        width: 100,
        height: 100,
        borderRadius: 12,
        resizeMode: 'cover',
        borderWidth: 2,
        borderColor: '#FF8C00',
    },
    productInfo: {
        paddingLeft: 18,
        flex: 1,
    },
    productTitle: {
        fontSize: 18,
        fontWeight: '700',
        marginBottom: 3,
        color: '#333',
    },
    productSize: {
        fontSize: 14,
        color: '#777',
        marginBottom: 6,
    },
    productPrice: {
        fontSize: 16,
        fontWeight: '700',
        color: '#FF8C00',
    },
    bottomNav: {
        position: 'absolute',
        bottom: 0,
        left: 0,
        right: 0,
        backgroundColor: '#FFFFFF',
        borderTopWidth: 1,
        borderTopColor: '#E0E0E0',
        paddingVertical: 12,
        elevation: 10,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: -4 },
        shadowOpacity: 0.15,
        shadowRadius: 6,
    },
    navContent: {
        flexDirection: 'row',
        justifyContent: 'space-around',
        alignItems: 'center',
    },
    navItem: {
        alignItems: 'center',
        paddingVertical: 5,
        paddingHorizontal: 10,
    },
    navText: {
        color: '#FF8C00',
        fontSize: 12,
        marginTop: 4,
        fontWeight: '600',
    },
    statusContainer: {
        flex: 1,
        justifyContent: 'center',
        alignItems: 'center',
        paddingVertical: 50,
    },
    statusText: {
        marginTop: 10,
        fontSize: 16,
        color: '#555',
        textAlign: 'center',
    },
    statusSubText: {
        fontSize: 14,
        color: '#888',
        textAlign: 'center',
        marginTop: 5,
    },
    retryButton: {
        backgroundColor: '#FF8C00',
        paddingVertical: 10,
        paddingHorizontal: 20,
        borderRadius: 8,
        marginTop: 15,
    },
    retryButtonText: {
        color: 'white',
        fontSize: 16,
        fontWeight: 'bold',
    },
    headerNew: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        paddingTop: 40,
        paddingHorizontal: 20,
        paddingBottom: 10,
        backgroundColor: '#fff',
        borderBottomWidth: 1,
        borderBottomColor: '#F0F0F0',
        elevation: 0,
    },
    headerTitle: {
        fontSize: 22,
        fontWeight: 'bold',
        color: '#222',
    },
    searchContainerNew: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#F6F6F6',
        marginHorizontal: 18,
        marginTop: 18,
        borderRadius: 16,
        paddingHorizontal: 16,
        paddingVertical: 8,
    },
    searchIconNew: {
        marginRight: 10,
    },
    searchInputNew: {
        flex: 1,
        color: '#333',
        fontSize: 16,
        paddingVertical: 8,
    },
    categoryRowNew: {
        flexDirection: 'row',
        justifyContent: 'flex-start',
        alignItems: 'center',
        marginTop: 18,
        marginBottom: 10,
        marginLeft: 18,
    },
    categoryPill: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#F6F6F6',
        borderRadius: 20,
        paddingHorizontal: 18,
        paddingVertical: 8,
        marginRight: 10,
    },
    categoryPillText: {
        fontSize: 14,
        color: '#222',
        fontWeight: '600',
    },
    productGrid: {
        flexDirection: 'row',
        flexWrap: 'wrap',
        justifyContent: 'space-between',
        paddingHorizontal: 18,
        paddingTop: 10,
    },
    productCardNew: {
        width: '47%',
        backgroundColor: '#fff',
        borderRadius: 18,
        marginBottom: 18,
        padding: 12,
        alignItems: 'center',
        elevation: 2,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.06,
        shadowRadius: 4,
        position: 'relative',
    },
    productImageWrapper: {
        width: 90,
        height: 90,
        borderRadius: 45,
        overflow: 'hidden',
        marginBottom: 10,
        position: 'relative',
    },
    productImageNew: {
        width: '100%',
        height: '100%',
        borderRadius: 45,
        resizeMode: 'cover',
    },
    favoriteIcon: {
        position: 'absolute',
        top: 6,
        right: 6,
        backgroundColor: '#fff',
        borderRadius: 12,
        padding: 2,
        elevation: 2,
    },
    productTitleNew: {
        fontSize: 15,
        fontWeight: 'bold',
        color: '#222',
        marginBottom: 2,
        textAlign: 'center',
    },
    productRowInfo: {
        flexDirection: 'row',
        alignItems: 'center',
        marginBottom: 2,
    },
    productInfoText: {
        fontSize: 12,
        color: '#888',
    },
    productPriceNew: {
        fontSize: 16,
        fontWeight: 'bold',
        color: '#E74C3C',
        marginTop: 2,
    },
});

export default HomeScreenWithNavigation;