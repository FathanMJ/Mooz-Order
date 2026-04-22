import React, { Component } from 'react';
import { View, Text, TextInput, TouchableOpacity, StyleSheet, SafeAreaView, Image, Alert } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { StackNavigationProp } from '@react-navigation/stack';
import { RootStackParamList } from '../navigation/AppNavigator';
import { authAPI } from '../../services/authAPI';
import AsyncStorage from '@react-native-async-storage/async-storage';
import Icon from 'react-native-vector-icons/Ionicons'; // Pastikan Anda sudah menginstal react-native-vector-icons

type NavigationProp = StackNavigationProp<RootStackParamList, 'Login'>;

interface LoginScreenProps {
  navigation: NavigationProp;
}

interface LoginScreenState {
  email: string;
  password: string;
  errorMessage: string;
  isLoading: boolean;
  showPassword: boolean;
}

interface LoginResponse {
  status: boolean;
  message: string;
  data?: {
    access_token: string;
    token_type: string;
    expires_in: number;
    user: {
      id: number;
      nama: string;
      email: string;
      no_hp: string;
      alamat: string;
      role: string;
    };
  };
}

class LoginScreen extends Component<LoginScreenProps, LoginScreenState> {
  constructor(props: LoginScreenProps) {
    super(props);
    this.state = {
      email: '',
      password: '',
      errorMessage: '',
      isLoading: false,
      showPassword: false
    };
  }

  private handleEmailChange = (text: string): void => {
    this.setState({ email: text, errorMessage: '' });
  };

  private handlePasswordChange = (text: string): void => {
    this.setState({ password: text, errorMessage: '' });
  };

  private toggleShowPassword = (): void => {
    this.setState(prevState => ({ showPassword: !prevState.showPassword }));
  };

  private validateForm = (): boolean => {
    const { email, password } = this.state;

    if (!email || !password) {
      this.setState({ errorMessage: 'Email dan password harus diisi' });
      return false;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      this.setState({ errorMessage: 'Format email tidak valid' });
      return false;
    }

    if (password.length < 6) {
      this.setState({ errorMessage: 'Password minimal 6 karakter' });
      return false;
    }

    return true;
  };

  private handleLogin = async (): Promise<void> => {
    if (!this.validateForm()) {
      return;
    }

    const { email, password } = this.state;
    this.setState({ isLoading: true, errorMessage: '' });

    try {
      const response = await authAPI.login(email, password);
      const loginResponse = response as LoginResponse;

      console.log('Login API response:', loginResponse);

      if (loginResponse.status && loginResponse.data) {
        await AsyncStorage.setItem('access_token', loginResponse.data.access_token);
        
        if (loginResponse.data.user !== undefined) {
              await AsyncStorage.setItem('user_data', JSON.stringify(loginResponse.data.user));
        }
        
        this.props.navigation.replace('HomeScreen', { 
          userName: loginResponse.data?.user?.nama || 'User'
        });
      } else {
        this.setState({ errorMessage: loginResponse.message || 'Login gagal' });
        console.error('Login failed with status: false', loginResponse);
      }
    } catch (error: any) {
      let errorMessage = 'Terjadi kesalahan saat login';
      
      if (error.response) {
        switch (error.response.status) {
          case 401:
            errorMessage = 'Email atau password salah';
            break;
          case 422:
            if (error.response.data?.errors) {
              const errors = error.response.data.errors as Record<string, string[]>;
              if (errors.email) errorMessage = errors.email[0];
              else if (errors.password) errorMessage = errors.password[0];
              else if (errors.nama) errorMessage = errors.nama[0];
              else if (errors.no_hp) errorMessage = errors.no_hp[0];
              else if (errors.alamat) errorMessage = errors.alamat[0];
              else errorMessage = Object.values(errors)[0][0] as string;
            } else {
              errorMessage = error.response.data?.message || 'Data yang dimasukkan tidak valid';
            }
            break;
          default:
            errorMessage = error.response.data?.message || errorMessage;
            console.error('Unhandled API error during login:', error.response.data);
            break;
        }
      } else if (error.request) {
          errorMessage = 'Tidak ada respons dari server. Periksa koneksi jaringan Anda.';
          console.error('No response received during login:', error.request);
      } else {
          errorMessage = error.message;
          console.error('Error setting up login request:', error.message);
      }

      this.setState({ errorMessage });
    } finally {
      this.setState({ isLoading: false });
    }
  };

  private handleForgotPassword = (): void => {
    this.props.navigation.navigate('ForgotPassword');
  };

  private handleRegister = (): void => {
    this.props.navigation.navigate('Register');
  };

  private handleGoBack = (): void => {
    this.props.navigation.goBack();
  };

  public render(): JSX.Element {
    const { email, password, errorMessage, isLoading, showPassword } = this.state;

    return (
      <SafeAreaView style={styles.container}>
        {/* Back button */}
        <TouchableOpacity 
          style={styles.backButton}
          onPress={this.handleGoBack}
        >
          <Icon name="arrow-back" size={28} color="#FF8C00" />
        </TouchableOpacity>

        <View style={styles.content}>
          <View style={styles.header}>
            <Text style={styles.title}>Masuk</Text>
            <Text style={styles.subtitle}>Selamat datang kembali!</Text>
          </View>

          <View style={styles.form}>
            {/* Email Input */}
            <View style={[styles.inputWrapper, errorMessage && styles.inputError]}>
                <TextInput
                    style={styles.input}
                    placeholder="Email"
                    placeholderTextColor="#999" // Warna placeholder lebih lembut
                    value={email}
                    onChangeText={this.handleEmailChange}
                    keyboardType="email-address"
                    autoCapitalize="none"
                    editable={!isLoading}
                />
            </View>

            {/* Password Input */}
            <View style={[styles.inputWrapper, styles.inputContainer, errorMessage && styles.inputError]}>
                <TextInput
                    style={styles.passwordInput}
                    placeholder="Kata Sandi"
                    placeholderTextColor="#999" // Warna placeholder lebih lembut
                    value={password}
                    onChangeText={this.handlePasswordChange}
                    secureTextEntry={!showPassword}
                    editable={!isLoading}
                />
                <TouchableOpacity onPress={this.toggleShowPassword} disabled={isLoading}>
                    <Icon name={showPassword ? 'eye-off' : 'eye'} size={22} color="#666" />
                </TouchableOpacity>
            </View>

            {/* Forgot Password Link */}
            <TouchableOpacity 
              style={styles.forgotPassword}
              onPress={this.handleForgotPassword}
              disabled={isLoading}
            >
              <Text style={styles.forgotPasswordText}>Lupa password?</Text>
            </TouchableOpacity>

            {/* Login Button */}
            <TouchableOpacity 
              style={[styles.loginButton, isLoading && styles.disabledButton]}
              onPress={this.handleLogin}
              disabled={isLoading}
            >
              <Text style={styles.loginButtonText}>
                {isLoading ? 'Memuat...' : 'Masuk'}
              </Text>
            </TouchableOpacity>

            {/* Error Message Display */}
            {errorMessage !== '' && (
              <Text style={styles.errorText}>{errorMessage}</Text>
            )}

            {/* Register Link */}
            <TouchableOpacity 
              style={styles.registerLink}
              onPress={this.handleRegister}
              disabled={isLoading}
            >
              <Text style={styles.registerLinkText}>
                Belum punya akun? <Text style={styles.registerLinkTextBold}>Daftar</Text>
              </Text>
            </TouchableOpacity>
          </View>
        </View>
      </SafeAreaView>
    );
  }
}

// Create a wrapper component to handle navigation
const LoginScreenWithNavigation = () => {
  const navigation = useNavigation<NavigationProp>();
  return <LoginScreen navigation={navigation} />;
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F8F8F8', // Latar belakang lebih lembut
  },
  backButton: {
    position: 'absolute',
    top: 45, // Sedikit lebih ke bawah
    left: 20,
    zIndex: 1,
    padding: 8, // Tambahkan padding untuk area sentuh yang lebih besar
  },
  backButtonText: {
    // Diganti dengan Icon, jadi style ini mungkin tidak terpakai jika menggunakan Icon
    fontSize: 28,
    color: '#FF8C00',
  },
  content: {
    flex: 1,
    paddingHorizontal: 25, // Padding horizontal sedikit lebih besar
    paddingTop: 100, // Padding atas lebih banyak untuk header
    justifyContent: 'center', // Pusatkan konten secara vertikal
    alignItems: 'center', // Pusatkan konten secara horizontal
  },
  header: {
    width: '100%', // Pastikan header mengambil lebar penuh
    marginBottom: 40, // Jarak lebih besar setelah header
    alignItems: 'flex-start', // Teks header rata kiri
  },
  title: {
    fontSize: 36, // Ukuran font lebih besar
    fontWeight: 'bold',
    color: '#333', // Warna teks judul lebih gelap agar kontras
    marginBottom: 8,
  },
  subtitle: {
    fontSize: 16,
    color: '#777', // Warna subtitle lebih lembut
  },
  form: {
    width: '100%',
    alignItems: 'center', // Pusatkan elemen form di dalam form container
  },
  inputWrapper: {
    backgroundColor: '#fff', // Latar belakang putih untuk input
    borderRadius: 12, // Sudut lebih membulat
    marginBottom: 15,
    width: '100%', // Input mengambil lebar penuh
    borderWidth: 1, // Border halus
    borderColor: '#E0E0E0', // Warna border lebih halus
    // Menambahkan bayangan untuk efek modern
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 2,
    },
    shadowOpacity: 0.08, // Bayangan lebih lembut
    shadowRadius: 4,
    elevation: 3, // Elevasi untuk Android
  },
  input: {
    padding: 16, // Padding lebih besar
    fontSize: 16,
    color: '#333', // Warna teks input lebih gelap
  },
  inputContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingRight: 16, // Padding kanan untuk ikon mata
  },
  passwordInput: {
    flex: 1,
    padding: 16, // Padding lebih besar
    fontSize: 16,
    color: '#333',
  },
  inputError: {
    borderColor: '#E53935', // Warna merah error yang lebih solid
    borderWidth: 2, // Border error lebih tebal
  },
  forgotPassword: {
    alignSelf: 'flex-end',
    marginBottom: 25, // Jarak lebih besar
  },
  forgotPasswordText: {
    color: '#FF8C00',
    fontSize: 15,
    fontWeight: '600', // Sedikit lebih tebal
  },
  loginButton: {
    backgroundColor: '#FF8C00',
    padding: 18, // Padding lebih besar
    borderRadius: 12, // Sudut lebih membulat
    alignItems: 'center',
    width: '100%', // Ambil lebar penuh
    marginBottom: 20, // Jarak setelah tombol
    // Bayangan yang lebih menonjol untuk tombol utama
    shadowColor: '#FF8C00',
    shadowOffset: {
      width: 0,
      height: 4,
    },
    shadowOpacity: 0.3,
    shadowRadius: 5,
    elevation: 6,
  },
  loginButtonText: {
    color: '#FFF',
    fontSize: 18,
    fontWeight: 'bold',
    letterSpacing: 0.5, // Sedikit spasi antar huruf
  },
  errorText: {
    color: '#E53935', // Warna merah error yang sama
    textAlign: 'center',
    marginBottom: 15,
    fontSize: 14,
    fontWeight: '500',
  },
  registerLink: {
    alignItems: 'center',
    marginTop: 10, // Jarak dari elemen di atasnya
  },
  registerLinkText: {
    color: '#666',
    fontSize: 15,
  },
  registerLinkTextBold: {
    color: '#FF8C00',
    fontWeight: 'bold',
  },
  disabledButton: {
    opacity: 0.5, // Opasitas lebih rendah saat disabled
    elevation: 0, // Hapus bayangan saat disabled
    shadowOpacity: 0,
  },
});

export default LoginScreenWithNavigation;