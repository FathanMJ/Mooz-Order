import React, { useState } from 'react';
import { View, Text, TextInput, TouchableOpacity, StyleSheet, Alert } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { StackNavigationProp } from '@react-navigation/stack';
import { RootStackParamList } from '../navigation/AppNavigator';
import { authAPI } from '../../services/authAPI';

export default function ForgotPasswordScreen() {
  const navigation = useNavigation<StackNavigationProp<RootStackParamList, 'ForgotPassword'>>();
  const [step, setStep] = useState<'email' | 'reset'>('email');
  const [email, setEmail] = useState('');
  const [otp, setOtp] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSendOtp = async () => {
    if (!email) {
      Alert.alert('Error', 'Email harus diisi');
      return;
    }
    setLoading(true);
    try {
      const response = await authAPI.sendOtp({ email, via: 'email' });
      if (response.status) {
        Alert.alert('Sukses', `Kode OTP telah dikirim ke ${email}`);
        setStep('reset');
      } else {
        Alert.alert('Error', response.message || 'Gagal mengirim OTP');
      }
    } catch (err) {
      Alert.alert('Error', 'Terjadi kesalahan saat mengirim OTP');
    } finally {
      setLoading(false);
    }
  };

  const handleResetPassword = async () => {
    if (!otp || !newPassword || !confirmPassword) {
      Alert.alert('Error', 'Semua field harus diisi');
      return;
    }
    if (newPassword !== confirmPassword) {
      Alert.alert('Error', 'Konfirmasi password tidak cocok');
      return;
    }
    setLoading(true);
    try {
      const response = await authAPI.resetPassword({
        email,
        otp,
        new_password: newPassword,
        new_password_confirmation: confirmPassword,
      });
      if (response.status) {
        Alert.alert('Sukses', 'Password berhasil direset, silakan login.');
        navigation.navigate('Login');
      } else {
        Alert.alert('Error', response.message || 'Gagal reset password');
      }
    } catch (err) {
      Alert.alert('Error', 'Terjadi kesalahan saat reset password');
    } finally {
      setLoading(false);
    }
  };

  return (
    <View style={styles.container}>
      <TouchableOpacity 
        style={styles.backButton}
        onPress={() => navigation.goBack()}
      >
        <Text style={styles.backButtonText}>←</Text>
      </TouchableOpacity>

      <View style={styles.content}>
        <Text style={styles.title}>Lupa Password</Text>
        {step === 'email' ? (
          <>
            <Text style={styles.subtitle}>
              Masukkan email Anda untuk menerima kode verifikasi
            </Text>
            <TextInput
              style={styles.input}
              placeholder="Masukan Email Anda"
              placeholderTextColor="#666"
              value={email}
              onChangeText={setEmail}
              keyboardType="email-address"
              autoCapitalize="none"
            />
            <TouchableOpacity
              style={styles.sendButton}
              onPress={handleSendOtp}
              disabled={loading}
            >
              <Text style={styles.sendButtonText}>{loading ? 'Mengirim...' : 'Kirim Kode'}</Text>
            </TouchableOpacity>
          </>
        ) : (
          <>
            <Text style={styles.subtitle}>
              Masukkan kode OTP yang dikirim ke email Anda dan password baru
            </Text>
            <TextInput
              style={styles.input}
              placeholder="Kode OTP"
              value={otp}
              onChangeText={setOtp}
              keyboardType="number-pad"
            />
            <TextInput
              style={styles.input}
              placeholder="Password Baru"
              value={newPassword}
              onChangeText={setNewPassword}
              secureTextEntry
            />
            <TextInput
              style={styles.input}
              placeholder="Konfirmasi Password Baru"
              value={confirmPassword}
              onChangeText={setConfirmPassword}
              secureTextEntry
            />
            <TouchableOpacity
              style={styles.sendButton}
              onPress={handleResetPassword}
              disabled={loading}
            >
              <Text style={styles.sendButtonText}>{loading ? 'Memproses...' : 'Reset Password'}</Text>
            </TouchableOpacity>
          </>
        )}
        <TouchableOpacity 
          style={styles.backToLogin}
          onPress={() => navigation.navigate('Login')}
        >
          <Text style={styles.backToLoginText}>Kembali ke Login</Text>
        </TouchableOpacity>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#ffffff',
  },
  backButton: {
    position: 'absolute',
    top: 40,
    left: 20,
    zIndex: 1,
  },
  backButtonText: {
    fontSize: 28,
    color: '#FF8C00',
  },
  content: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 24,
  },
  title: {
    fontSize: 28,
    fontWeight: 'bold',
    color: '#FF8C00',
    marginBottom: 8,
    textAlign: 'center',
  },
  subtitle: {
    fontSize: 15,
    color: '#888',
    marginBottom: 28,
    textAlign: 'center',
    lineHeight: 22,
  },
  input: {
    backgroundColor: '#f5f5f5',
    padding: 15,
    borderRadius: 10,
    marginBottom: 20,
    fontSize: 16,
  },
  sendButton: {
    backgroundColor: '#FF8C00',
    padding: 15,
    borderRadius: 10,
    alignItems: 'center',
    marginBottom: 15,
  },
  sendButtonText: {
    color: '#FFF',
    fontSize: 18,
    fontWeight: 'bold',
  },
  backToLogin: {
    alignItems: 'center',
  },
  backToLoginText: {
    color: '#FF8C00',
    fontSize: 16,
  },
});
