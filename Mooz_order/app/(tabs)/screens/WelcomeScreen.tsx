import React, { Component } from 'react';
import { View, Text, StyleSheet, TouchableOpacity, Image, SafeAreaView } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { StackNavigationProp } from '@react-navigation/stack';
import { RootStackParamList } from '../navigation/AppNavigator';

type NavigationProp = StackNavigationProp<RootStackParamList, 'Welcome'>;

interface WelcomeScreenProps {
  navigation: NavigationProp;
}

interface WelcomeScreenState {
  // Add state properties here if needed
}

class WelcomeScreen extends Component<WelcomeScreenProps, WelcomeScreenState> {
  constructor(props: WelcomeScreenProps) {
    super(props);
    this.state = {
      // Initialize state here if needed
    };
  }

  private handleRegister = (): void => {
    this.props.navigation.navigate('Register');
  };

  private handleLogin = (): void => {
    this.props.navigation.navigate('Login');
  };

  public render(): JSX.Element {
    return (
      <SafeAreaView style={styles.container}>
        <View style={styles.content}>
          <View style={styles.header}>
            {/* Mengganti Text 'MOOZ ORDER' dengan komponen Image untuk logo */}
            <Image 
              source={require('../assets/images/Logo_MoozOrder.png')} // Sesuaikan path jika berbeda
              style={styles.logo} 
              accessibilityLabel="MOOZ ORDER Logo" // Aksesibilitas
            />
            <Text style={styles.subtitle}>Selamat datang di Aplikasi Pemesanan Mooz Order !!!</Text>
          </View>
            
          {/* Anda bisa menambahkan ilustrasi tambahan di sini jika diinginkan */}
          {/* <Image 
            source={require('../assets/images/welcome_illustration.png')} // Sesuaikan path gambar Anda
            style={styles.illustration} 
          /> */}

          <View style={styles.buttonContainer}>
            <TouchableOpacity 
              style={styles.buttonPrimary}
              onPress={this.handleRegister}
            >
              <Text style={styles.buttonPrimaryText}>Daftar</Text>
            </TouchableOpacity>

            <TouchableOpacity 
              style={styles.buttonSecondary}
              onPress={this.handleLogin}
            >
              <Text style={styles.buttonSecondaryText}>Sudah punya akun? Masuk</Text>
            </TouchableOpacity>
          </View>
        </View>
      </SafeAreaView>
    );
  }
}

// Create a wrapper component to handle navigation
const WelcomeScreenWithNavigation = () => {
  const navigation = useNavigation<NavigationProp>();
  return <WelcomeScreen navigation={navigation} />;
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F8F8F8',
  },
  content: {
    flex: 1,
    justifyContent: 'space-between',
    paddingHorizontal: 30,
    paddingVertical: 50,
  },
  header: {
    alignItems: 'center',
    marginTop: 80,
    marginBottom: 40,
  },
  // --- Gaya untuk Logo ---
  logo: {
    width: 350, // Sesuaikan lebar logo
    height: 200, // Sesuaikan tinggi logo
    resizeMode: 'contain', // Penting agar logo tidak terdistorsi
    marginBottom: 10, // Jarak antara logo dan subtitle
  },
  // --- END Gaya untuk Logo ---
  
  subtitle: {
    fontSize: 18,
    color: '#777',
    textAlign: 'center',
    lineHeight: 25,
  },
  // illustration: { // Gaya untuk gambar ilustrasi (jika Anda menambahkannya)
  //   width: '90%',
  //   height: 250,
  //   resizeMode: 'contain',
  //   alignSelf: 'center',
  //   marginBottom: 30,
  // },
  buttonContainer: {
    width: '100%',
    marginBottom: 20,
  },
  buttonPrimary: {
    backgroundColor: '#FF8C00',
    paddingVertical: 18,
    borderRadius: 12,
    width: '100%',
    alignItems: 'center',
    marginBottom: 15,
    shadowColor: '#FF8C00',
    shadowOffset: {
      width: 0,
      height: 6,
    },
    shadowOpacity: 0.35,
    shadowRadius: 8,
    elevation: 8,
  },
  buttonPrimaryText: {
    color: '#FFF',
    fontSize: 18,
    fontWeight: 'bold',
    letterSpacing: 0.8,
  },
  buttonSecondary: {
    backgroundColor: '#FFF',
    paddingVertical: 18,
    borderRadius: 12,
    width: '100%',
    alignItems: 'center',
    borderWidth: 1.5,
    borderColor: '#FF8C00',
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 2,
    },
    shadowOpacity: 0.08,
    shadowRadius: 4,
    elevation: 3,
  },
  buttonSecondaryText: {
    color: '#FF8C00',
    fontSize: 18,
    fontWeight: 'bold',
    letterSpacing: 0.8,
  },
});

export default WelcomeScreenWithNavigation;