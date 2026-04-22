import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage'; // Pastikan import ini ada dan benar

// --- PENTING: PASTIKAN API_URL INI SELALU TERBARU DENGAN NGROK URL ANDA ---
// Jika URL ngrok Anda berubah, Anda HARUS memperbarui ini.
const API_URL = 'https://67dd-103-137-35-97.ngrok-free.app/api'; // PASTIKAN INI URL NGROK ANDA YANG AKTIF!

const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    'ngrok-skip-browser-warning': 'true',
  },
});

// Interceptor untuk menyisipkan token Bearer di setiap request yang memerlukan otentikasi
api.interceptors.request.use(
  async (config) => {
    // Menggunakan kunci 'token' yang konsisten untuk mengambil access_token
    const token = await AsyncStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    config.headers['ngrok-skip-browser-warning'] = 'true';
    return config;
  },
  (error) => Promise.reject(error)
);

// AUTH API
export const authAPI = {
  login: async (email: string, password: string) => {
    try {
      const response = await api.post('/login', { email, password });
      if (response.data.status && response.data.data.access_token) {
        // Memastikan kunci 'token' digunakan untuk menyimpan access_token
        await AsyncStorage.setItem('token', response.data.data.access_token);
        await AsyncStorage.setItem('user', JSON.stringify(response.data.data.user));
      }
      return response.data;
    } catch (error: any) {
      console.error('Login error:', error.response?.data || error.message || error);
      // Penanganan error yang lebih spesifik dan user-friendly
      if (axios.isAxiosError(error) && error.response) {
        if (error.response.status === 401) {
          throw new Error('Email atau password salah. Silakan coba lagi.');
        } else if (error.response.status === 422 && error.response.data.errors) {
          const errors = error.response.data.errors;
          const firstErrorKey = Object.keys(errors)[0];
          const firstErrorMessage = Array.isArray(errors[firstErrorKey])
            ? errors[firstErrorKey][0]
            : errors[firstErrorKey];
          throw new Error(`Data tidak valid: ${firstErrorMessage}`);
        } else {
          throw new Error(error.response.data.message || 'Terjadi kesalahan saat login.');
        }
      } else {
        throw new Error('Terjadi kesalahan jaringan atau tak terduga saat login.');
      }
    }
  },

  register: async (
    nama: string,
    email: string,
    password: string,
    no_hp: string = '',
    alamat: string = '',
    role: string = 'user'
  ) => {
    try {
      const response = await api.post('/register', {
        nama,
        email,
        password,
        password_confirmation: password,
        no_hp,
        alamat,
        role,
      });
      return response.data;
    } catch (error: any) {
      console.error('Registration error:', error.response?.data || error.message || error);
      if (axios.isAxiosError(error) && error.response) {
        if (error.response.status === 422 && error.response.data.errors) {
          const errors = error.response.data.errors;
          const firstErrorKey = Object.keys(errors)[0];
          const firstErrorMessage = Array.isArray(errors[firstErrorKey])
            ? errors[firstErrorKey][0]
            : errors[firstErrorKey];
          throw new Error(`Validasi gagal: ${firstErrorMessage}`);
        } else {
          throw new Error(error.response.data.message || 'Terjadi kesalahan saat registrasi.');
        }
      } else {
        throw new Error('Terjadi kesalahan jaringan atau tak terduga saat registrasi.');
      }
    }
  },

  logout: async () => {
    try {
      // Pastikan endpoint logout Laravel memang membutuhkan token (biasanya iya)
      await api.post('/auth/logout');
    } catch (error) {
      // Logout di frontend tetap dilakukan meskipun API call gagal (misal: token expired)
      console.error('Logout API call failed:', error);
    } finally {
      // Menggunakan kunci 'token' yang konsisten untuk menghapus
      await AsyncStorage.removeItem('token');
      await AsyncStorage.removeItem('user');
    }
  },

  getProfile: async () => {
    try {
      const response = await api.get('/auth/me');
      return response.data;
    } catch (error: any) {
      console.error('Error fetching profile:', error.response?.data || error.message || error);
      throw error;
    }
  },

  changePassword: async (data: { current_password: string; new_password: string; new_password_confirmation: string }) => {
    try {
      // Token sudah otomatis disisipkan oleh interceptor
      const response = await api.post('/auth/change-password', data);
      return response.data;
    } catch (error: any) {
      console.error('Change password error:', error.response?.data || error.message || error);
      if (axios.isAxiosError(error) && error.response) {
        if (error.response.status === 422 && error.response.data.errors) {
          const errors = error.response.data.errors;
          const firstErrorKey = Object.keys(errors)[0];
          const firstErrorMessage = Array.isArray(errors[firstErrorKey])
            ? errors[firstErrorKey][0]
            : errors[firstErrorKey];
          throw new Error(`Validasi gagal: ${firstErrorMessage}`);
        } else {
          throw new Error(error.response.data.message || 'Terjadi kesalahan saat mengubah password.');
        }
      } else {
        throw new Error('Terjadi kesalahan jaringan atau tak terduga saat mengubah password.');
      }
    }
  },
};

// Produk API
export const productAPI = {
  getAll: async () => {
    try {
      const response = await api.get('/produk');
      return response.data;
    } catch (error: any) {
      console.error('Error fetching products:', error.response?.data || error.message || error);
      throw error;
    }
  },

  getById: async (id: string) => {
    try {
      const response = await api.get(`/produk/${id}`);
      return response.data;
    } catch (error: any) {
      console.error(`Error fetching product with ID ${id}:`, error.response?.data || error.message || error);
      throw error;
    }
  },

  create: async (productData: any) => {
    try {
      const response = await api.post('/produk', productData);
      return response.data;
    } catch (error: any) {
      console.error('Error creating product:', error.response?.data || error.message || error);
      throw error;
    }
  },

  update: async (id: string, productData: any) => {
    try {
      const response = await api.put(`/produk/${id}`, productData);
      return response.data;
    } catch (error: any) {
      console.error(`Error updating product with ID ${id}:`, error.response?.data || error.message || error);
      throw error;
    }
  },

  delete: async (id: string) => {
    try {
      const response = await api.delete(`/produk/${id}`);
      return response.data;
    } catch (error: any) {
      console.error(`Error deleting product with ID ${id}:`, error.response?.data || error.message || error);
      throw error;
    }
  },
};

// Order API
export const orderAPI = {
  // ... (metode create, getAll, getById, updateStatus yang sudah ada) ...

  create: async (orderData: {
    items: {
      id: string;
      quantity: number;
      size?: string;
      price?: number;
      name?: string;
    }[];
    total_amount: number;
    customer_details: {
      first_name: string;
      email: string;
      phone: string;
    };
    catatan?: string | null;
    user_id: number;
  }) => {
    try {
      console.log('Sending order data to backend:', JSON.stringify(orderData, null, 2));

      const response = await api.post('/payment/checkout', orderData);
      return response.data;
    } catch (error: any) {
      console.error('Error creating order/checkout:', error.response?.data || error.message || error);
      if (axios.isAxiosError(error) && error.response) {
        if (error.response.status === 500) {
          throw new Error(
            error.response.data.message || 'Terjadi kesalahan server saat checkout. Mohon coba lagi nanti.'
          );
        } else if (error.response.data.message) {
          throw new Error(error.response.data.message);
        }
      }
      throw new Error('Terjadi kesalahan tidak terduga saat checkout.');
    }
  },

  getAll: async () => {
    try {
      const response = await api.get('/orders');
      return response.data;
    } catch (error: any) {
      console.error('Error fetching all orders:', error.response?.data || error.message || error);
      throw error;
    }
  },

  getById: async (id: string) => {
    try {
      const response = await api.get(`/orders/${id}`);
      return response.data;
    } catch (error: any) {
      console.error(`Error fetching order with ID ${id}:`, error.response?.data || error.message || error);
      throw error;
    }
  },

  updateStatus: async (id: string, status: string) => {
    try {
      const response = await api.put(`/orders/${id}/status`, { status });
      return response.data;
    } catch (error: any) {
      console.error(`Error updating order status for ID ${id}:`, error.response?.data || error.message || error);
      throw error;
    }
  },

  /**
   * Mengambil daftar pesanan yang sudah selesai untuk pengguna yang sedang login.
   * @returns Promise<any> Daftar pesanan yang sudah selesai.
   */
  getCompletedOrders: async () => { // <--- TAMBAHKAN INI
    try {
      // Panggil endpoint baru di backend
      const response = await api.get('/orders/completed');
      return response.data.completed_orders; // Pastikan mengembalikan data yang benar dari respons JSON
    } catch (error: any) {
      console.error('Error fetching completed orders:', error.response?.data || error.message || error);
      if (axios.isAxiosError(error) && error.response && error.response.status === 401) {
        throw new Error('Sesi Anda berakhir. Silakan login ulang.');
      }
      throw new Error(error.response?.data?.error || 'Gagal memuat riwayat pesanan.');
    }
  },
};

// Profile API
export const profileAPI = {
  getProfile: async () => {
    try {
      const response = await api.get('/auth/me');
      return response.data;
    } catch (error: any) {
      console.error('Error fetching profile:', error.response?.data || error.message || error);
      throw error;
    }
  },

  updateProfile: async (profileData: {
    nama?: string;
    email?: string;
    no_hp?: string;
    alamat?: string;
  }) => {
    try {
      const response = await api.put('/auth/profile', profileData);
      return response.data;
    } catch (error: any) {
      console.error('Error updating profile:', error.response?.data || error.message || error);
      throw error;
    }
  },

  updateProfileImage: async (imageUri: string) => {
    try {
      const formData = new FormData();
      // Pastikan nama field di backend sesuai dengan 'profile_image'
      formData.append('profile_image', {
        uri: imageUri,
        type: 'image/jpeg', // Sesuaikan tipe jika gambar bisa format lain
        name: 'profile.jpg' // Nama file yang akan dikirim ke server
      } as any); // 'as any' digunakan karena tipe FormData.append bisa lebih ketat dari yang Anda inginkan untuk objek file

      const response = await api.post('/auth/profile/image', formData, {
        headers: {
          'Content-Type': 'multipart/form-data', // Penting untuk upload file
        },
      });
      return response.data;
    } catch (error: any) {
      console.error('Error updating profile image:', error.response?.data || error.message || error);
      throw error;
    }
  }
};

export default api;