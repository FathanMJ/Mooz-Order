import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';

// UBAH INI: Gunakan URL Ngrok Anda yang terbaru
const API_URL = 'https://a17d-36-72-135-87.ngrok-free.app/api'; // <--- UBAH DI SINI!

export const authAPI = {
  async login(email: string, password: string) {
    try {
      console.log('Attempting login to:', `${API_URL}/login`);
      const response = await axios.post(`${API_URL}/login`, {
        email,
        password,
      });

      console.log('Login response:', response.data);

      if (response.data.status) {
        await AsyncStorage.setItem('token', response.data.data.access_token);
        await AsyncStorage.setItem('user_data', JSON.stringify(response.data.data.user));
        return response.data;
      } else {
        throw new Error(response.data.message || 'Login gagal');
      }
    } catch (error: any) {
      console.error('Login error:', error.response?.data || error);
      if (error.response?.status === 401) {
        throw { message: 'Email atau password salah' };
      } else if (error.response?.status === 422) {
        throw { message: error.response.data.message || 'Data tidak valid' };
      } else {
        throw { message: error.response?.data?.message || 'Terjadi kesalahan saat login' };
      }
    }
  },

  async register(nama: string, email: string, password: string, no_hp: string = '-', alamat: string = '-', role: string = 'user') {
    try {
      console.log('Attempting registration to:', `${API_URL}/register`);
      const response = await axios.post(`${API_URL}/register`, {
        nama,
        email,
        password,
        password_confirmation: password,
        no_hp,
        alamat,
        role
      });

      console.log('Registration response:', response.data);
      return response.data;
    } catch (error: any) {
      console.error('Registration error:', error.response?.data || error);
      if (error.response?.status === 422) {
        const errors = error.response.data.errors;
        if (errors) {
          const firstError = Object.values(errors)[0];
          throw { message: Array.isArray(firstError) ? firstError[0] : firstError };
        }
      }
      throw { message: error.response?.data?.message || 'Terjadi kesalahan saat registrasi' };
    }
  },

  async logout() {
    try {
      const token = await AsyncStorage.getItem('token');
      if (token) {
        await axios.post(`${API_URL}/auth/logout`, {}, {
          headers: { Authorization: `Bearer ${token}` }
        });
      }
      await AsyncStorage.removeItem('token');
      await AsyncStorage.removeItem('user_data');
    } catch (error) {
      console.error('Logout error:', error);
      throw error;
    }
  },

  async sendOtp(data: { email?: string; via: 'email' | 'wa'; no_hp?: string }) {
    try {
      console.log('Attempting to send OTP to:', `${API_URL}/send-otp`);
      const response = await axios.post(`${API_URL}/send-otp`, data);

      console.log('Send OTP response:', response.data);
      return response.data;
    } catch (error: any) {
      console.error('Send OTP error:', error.response?.data || error);
      if (error.response?.status === 422) {
        const errors = error.response.data.errors;
        if (errors) {
          const firstError = Object.values(errors)[0];
          throw { message: Array.isArray(firstError) ? firstError[0] : firstError };
        }
      }
      throw { message: error.response?.data?.message || 'Terjadi kesalahan saat mengirim OTP' };
    }
  },

  async resetPassword(data: { email: string; otp: string; new_password: string; new_password_confirmation: string }) {
    try {
      console.log('Attempting to reset password to:', `${API_URL}/reset-password`);
      const response = await axios.post(`${API_URL}/reset-password`, data);

      console.log('Reset password response:', response.data);
      return response.data;
    } catch (error: any) {
      console.error('Reset password error:', error.response?.data || error);
      if (error.response?.status === 422) {
        const errors = error.response.data.errors;
        if (errors) {
          const firstError = Object.values(errors)[0];
          throw { message: Array.isArray(firstError) ? firstError[0] : firstError };
        }
      }
      throw { message: error.response?.data?.message || 'Terjadi kesalahan saat reset password' };
    }
  },

  async changePassword(data: { current_password: string; new_password: string; new_password_confirmation: string }) {
    try {
      const token = await AsyncStorage.getItem('token');
      if (!token) {
        throw { message: 'Token tidak ditemukan' };
      }

      console.log('Attempting to change password to:', `${API_URL}/auth/change-password`);
      const response = await axios.post(`${API_URL}/auth/change-password`, data, {
        headers: { Authorization: `Bearer ${token}` }
      });

      console.log('Change password response:', response.data);
      return response.data;
    } catch (error: any) {
      console.error('Change password error:', error.response?.data || error);
      if (error.response?.status === 422) {
        const errors = error.response.data.errors;
        if (errors) {
          const firstError = Object.values(errors)[0];
          throw { message: Array.isArray(firstError) ? firstError[0] : firstError };
        }
      }
      throw { message: error.response?.data?.message || 'Terjadi kesalahan saat mengubah password' };
    }
  }
};