import React, { Component } from 'react';
import { View, Text, TextInput, TouchableOpacity, StyleSheet, Image, Alert, ScrollView } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { StackNavigationProp } from '@react-navigation/stack';
import { RootStackParamList } from '../navigation/AppNavigator';
import { authAPI } from '../../services/authAPI';
import AsyncStorage from '@react-native-async-storage/async-storage';
import Icon from 'react-native-vector-icons/Ionicons'; // Pastikan Anda sudah menginstal react-native-vector-icons

type NavigationProp = StackNavigationProp<RootStackParamList, 'Register'>;

interface RegisterScreenProps {
  navigation: NavigationProp;
}

interface RegisterScreenState {
  nama: string;
  email: string;
  no_hp: string;
  alamat: string;
  password: string;
  confirmPassword: string;
  errorMessage: string;
  isLoading: boolean;
  showPassword: boolean;
  showConfirmPassword: boolean;
}

interface RegisterResponse {
  status: boolean;
  message: string;
  data?: {
    user: {
      id: number;
      nama: string;
      email: string;
      no_hp: string;
      alamat: string;
      role: string;
    };
    access_token: string;
    token_type: string;
    expires_in: number;
  };
}

class RegisterScreen extends Component<RegisterScreenProps, RegisterScreenState> {
  constructor(props: RegisterScreenProps) {
    super(props);
    this.state = {
      nama: '',
      email: '',
      no_hp: '',
      alamat: '',
      password: '',
      confirmPassword: '',
      errorMessage: '',
      isLoading: false,
      showPassword: false,
      showConfirmPassword: false
    };
  }

  private handleNameChange = (text: string): void => {
    this.setState({ nama: text, errorMessage: '' });
  };

  private handleEmailChange = (text: string): void => {
    this.setState({ email: text, errorMessage: '' });
  };

  private handlePhoneChange = (text: string): void => {
    this.setState({ no_hp: text, errorMessage: '' });
  };

  private handleAddressChange = (text: string): void => {
    this.setState({ alamat: text, errorMessage: '' });
  };

  private handlePasswordChange = (text: string): void => {
    this.setState({ password: text, errorMessage: '' });
  };

  private handleConfirmPasswordChange = (text: string): void => {
    this.setState({ confirmPassword: text, errorMessage: '' });
  };

  private toggleShowPassword = (): void => {
    this.setState(prevState => ({ showPassword: !prevState.showPassword }));
  };

  private toggleShowConfirmPassword = (): void => {
    this.setState(prevState => ({ showConfirmPassword: !prevState.showConfirmPassword }));
  };

  private validateForm = (): boolean => {
    const { nama, email, no_hp, alamat, password, confirmPassword } = this.state;

    if (!nama || !email || !no_hp || !alamat || !password || !confirmPassword) {
      this.setState({ errorMessage: 'Semua field harus diisi' });
      return false;
    }

    if (nama.length > 255) {
      this.setState({ errorMessage: 'Nama maksimal 255 karakter' });
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

    if (password !== confirmPassword) {
      this.setState({ errorMessage: 'Password dan konfirmasi password tidak cocok' });
      return false;
    }

    return true;
  };

  private handleRegister = async (): Promise<void> => {
    if (!this.validateForm()) {
      Alert.alert('Validasi Gagal', this.state.errorMessage);
      return;
    }

    const { nama, email, no_hp, alamat, password } = this.state;
    this.setState({ isLoading: true, errorMessage: '' });

    try {
      const response = await authAPI.register(
        nama,
        email,
        password,
        no_hp,
        alamat
      );

      const registerResponse = response as RegisterResponse;

      console.log('Register API response:', registerResponse);

      if (registerResponse.status) {
        Alert.alert(
          'Berhasil',
          registerResponse.message || 'Registrasi berhasil! Silakan login.',
          [
            {
              text: 'OK',
              onPress: () => this.props.navigation.navigate('Login')
            }
          ]
        );
      } else {
        Alert.alert('Registrasi Gagal', registerResponse.message || 'Registrasi gagal');
        console.error('Registration failed with status: false', registerResponse);
      }
    } catch (error: any) {
      console.error('Registration error:', error.response?.data || error);
      let displayMessage = 'Terjadi kesalahan saat registrasi';

      if (error.message) {
        displayMessage = error.message;
      } else if (error.response?.data?.message) {
        displayMessage = error.response.data.message;
      } else if (typeof error === 'string') {
        displayMessage = error;
      }

      Alert.alert('Registrasi Gagal', displayMessage);

    } finally {
      this.setState({ isLoading: false });
    }
  };

  private handleLogin = (): void => {
    this.props.navigation.navigate('Login');
  };

  private handleGoBack = (): void => {
    this.props.navigation.goBack();
  };

  public render(): JSX.Element {
    const { nama, email, no_hp, alamat, password, confirmPassword, errorMessage, isLoading, showPassword, showConfirmPassword } = this.state;

    return (
      <ScrollView style={styles.container} contentContainerStyle={styles.scrollViewContent}>
        {/* Back button */}
        <TouchableOpacity 
          style={styles.backButton}
          onPress={this.handleGoBack}
        >
          <Icon name="arrow-back" size={28} color="#FF8C00" />
        </TouchableOpacity>

        <View style={styles.content}>
          <Text style={styles.title}>Daftar Akun</Text>
          <Text style={styles.subtitle}>Buat akun baru untuk melanjutkan</Text>
            
          {/* Nama Lengkap Input */}
          <View style={[styles.inputWrapper, errorMessage && styles.inputError]}>
              <TextInput
                  style={styles.inputField}
                  placeholder="Nama Lengkap"
                  placeholderTextColor="#999"
                  value={nama}
                  onChangeText={this.handleNameChange}
                  editable={!isLoading}
              />
          </View>

          {/* Email Input */}
          <View style={[styles.inputWrapper, errorMessage && styles.inputError]}>
              <TextInput
                  style={styles.inputField}
                  placeholder="Email"
                  placeholderTextColor="#999"
                  value={email}
                  onChangeText={this.handleEmailChange}
                  keyboardType="email-address"
                  autoCapitalize="none"
                  editable={!isLoading}
              />
          </View>

          {/* Nomor HP Input */}
          <View style={[styles.inputWrapper, errorMessage && styles.inputError]}>
              <TextInput
                  style={styles.inputField}
                  placeholder="Nomor HP"
                  placeholderTextColor="#999"
                  value={no_hp}
                  onChangeText={this.handlePhoneChange}
                  keyboardType="phone-pad"
                  editable={!isLoading}
              />
          </View>

          {/* Alamat Input */}
          <View style={[styles.inputWrapper, errorMessage && styles.inputError]}>
              <TextInput
                  style={styles.inputField}
                  placeholder="Alamat"
                  placeholderTextColor="#999"
                  value={alamat}
                  onChangeText={this.handleAddressChange}
                  multiline
                  numberOfLines={3} // Menampilkan 3 baris secara default
                  textAlignVertical="top" // Agar teks mulai dari atas untuk multiline
                  editable={!isLoading}
              />
          </View>

          {/* Kata Sandi Input */}
          <View style={[styles.inputWrapper, styles.inputContainer, errorMessage && styles.inputError]}>
              <TextInput
                  style={styles.passwordInputField}
                  placeholder="Kata Sandi"
                  placeholderTextColor="#999"
                  value={password}
                  onChangeText={this.handlePasswordChange}
                  secureTextEntry={!showPassword}
                  editable={!isLoading}
              />
              <TouchableOpacity onPress={this.toggleShowPassword} disabled={isLoading}>
                  <Icon name={showPassword ? 'eye-off' : 'eye'} size={22} color="#666" />
              </TouchableOpacity>
          </View>

          {/* Konfirmasi Kata Sandi Input */}
          <View style={[styles.inputWrapper, styles.inputContainer, errorMessage && styles.inputError]}>
              <TextInput
                  style={styles.passwordInputField}
                  placeholder="Konfirmasi Kata Sandi"
                  placeholderTextColor="#999"
                  value={confirmPassword}
                  onChangeText={this.handleConfirmPasswordChange}
                  secureTextEntry={!showConfirmPassword}
                  editable={!isLoading}
              />
              <TouchableOpacity onPress={this.toggleShowConfirmPassword} disabled={isLoading}>
                  <Icon name={showConfirmPassword ? 'eye-off' : 'eye'} size={22} color="#666" />
              </TouchableOpacity>
          </View>

          {/* Register Button */}
          <TouchableOpacity 
            style={[styles.registerButton, isLoading && styles.disabledButton]}
            onPress={this.handleRegister}
            disabled={isLoading}
          >
            <Text style={styles.registerButtonText}>
              {isLoading ? 'Memuat...' : 'Daftar'}
            </Text>
          </TouchableOpacity>

          {/* Error Message Display */}
          {errorMessage !== '' && (
            <Text style={styles.errorText}>{errorMessage}</Text>
          )}

          {/* Login Link */}
          <TouchableOpacity 
            style={styles.loginLink}
            onPress={this.handleLogin}
            disabled={isLoading}
          >
            <Text style={styles.loginLinkText}>
              Sudah punya akun? <Text style={styles.loginLinkTextBold}>Masuk</Text>
            </Text>
          </TouchableOpacity>
        </View>
      </ScrollView>
    );
  }
}

const RegisterScreenWithNavigation = () => {
  const navigation = useNavigation<NavigationProp>();
  return <RegisterScreen navigation={navigation} />;
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F8F8F8', // Latar belakang yang lebih lembut
  },
  scrollViewContent: {
    flexGrow: 1, // Pastikan ScrollView bisa tumbuh untuk mengisi ruang
    justifyContent: 'center', // Pusatkan konten saat tidak perlu di-scroll
    paddingVertical: 50, // Padding vertikal untuk keseluruhan konten
  },
  backButton: {
    position: 'absolute',
    top: 45, // Sedikit lebih ke bawah
    left: 20,
    zIndex: 1,
    padding: 8, // Tambahkan padding untuk area sentuh yang lebih besar
  },
  backButtonText: {
    // Diganti dengan Icon, jadi style ini tidak terpakai
    fontSize: 28,
    color: '#FF8C00',
  },
  content: {
    paddingHorizontal: 25, // Padding horizontal lebih besar
    paddingTop: 0, // Padding atas sudah di handle ScrollViewContent
    paddingBottom: 0, // Padding bawah sudah di handle ScrollViewContent
    width: '100%', // Pastikan content mengambil lebar penuh
    alignSelf: 'center', // Pusatkan konten di ScrollView
  },
  title: {
    fontSize: 32,
    fontWeight: 'bold',
    color: '#333', // Warna teks judul lebih gelap
    marginBottom: 8,
    textAlign: 'center', // Pusatkan judul
  },
  subtitle: {
    fontSize: 16,
    color: '#777', // Warna subtitle lebih lembut
    marginBottom: 30, // Jarak lebih besar setelah subtitle
    textAlign: 'center', // Pusatkan subtitle
  },
  inputWrapper: {
    backgroundColor: '#fff', // Latar belakang putih untuk input
    borderRadius: 12, // Sudut lebih membulat
    marginBottom: 15,
    width: '100%',
    borderWidth: 1, // Border halus
    borderColor: '#E0E0E0', // Warna border lebih halus
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 2,
    },
    shadowOpacity: 0.08, // Bayangan lebih lembut
    shadowRadius: 4,
    elevation: 3,
  },
  inputField: { // Gaya untuk TextInput individu
    padding: 16,
    fontSize: 16,
    color: '#333',
  },
  inputContainer: { // Digunakan untuk input password dengan ikon
    flexDirection: 'row',
    alignItems: 'center',
    paddingRight: 16,
  },
  passwordInputField: { // Gaya untuk TextInput password di dalam inputContainer
    flex: 1,
    padding: 16,
    fontSize: 16,
    color: '#333',
  },
  inputError: {
    borderColor: '#E53935', // Warna merah error yang lebih solid
    borderWidth: 2, // Border error lebih tebal
  },
  // googleButton dan googleIcon/Text dihapus karena tidak digunakan
  registerButton: {
    backgroundColor: '#FF8C00',
    padding: 18, // Padding lebih besar
    borderRadius: 12, // Sudut membulat
    alignItems: 'center',
    width: '100%',
    marginBottom: 20, // Jarak setelah tombol
    shadowColor: '#FF8C00',
    shadowOffset: {
      width: 0,
      height: 4,
    },
    shadowOpacity: 0.3,
    shadowRadius: 5,
    elevation: 6,
  },
  registerButtonText: {
    color: '#FFF',
    fontSize: 18,
    fontWeight: 'bold',
    letterSpacing: 0.5,
  },
  loginLink: {
    alignItems: 'center',
    marginTop: 10,
  },
  loginLinkText: {
    color: '#666',
    fontSize: 15,
  },
  loginLinkTextBold: {
    color: '#FF8C00',
    fontWeight: 'bold',
  },
  errorText: {
    color: '#E53935',
    textAlign: 'center',
    marginBottom: 15,
    fontSize: 14,
    fontWeight: '500',
  },
  disabledButton: {
    opacity: 0.5,
    elevation: 0,
    shadowOpacity: 0,
  },
});

export default RegisterScreenWithNavigation;