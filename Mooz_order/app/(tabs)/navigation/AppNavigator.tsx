// app/(tabs)/navigation/AppNavigator.tsx
import React from 'react';
import { createStackNavigator } from '@react-navigation/stack';
import { ImageSourcePropType } from 'react-native'; // Pastikan ini diimpor dari react-native

// Import semua layar yang akan digunakan dalam navigator
import WelcomeScreen from '../screens/WelcomeScreen';
import RegisterScreen from '../screens/RegisterScreen';
import LoginScreen from '../screens/LoginScreen';
import ForgotPasswordScreen from '../screens/ForgotPasswordScreen';
import HomeScreen from '../screens/HomeScreen';
import DetailScreen from '../screens/DetailScreen';
import CartScreen from '../screens/CartScreen';
import NotifikasiScreen from '../screens/notifikasiScreen'; // Pastikan nama file ini adalah 'notifikasiScreen.tsx'
import RiwayatScreen from '../screens/RiwayatScreen';
import ProfileScreen from '../screens/ProfileScreen';

import { OrderProvider } from '../context/OrderContext';

// Definisi tipe untuk parameter rute
export type RootStackParamList = {
  Welcome: undefined;
  Register: undefined;
  Login: undefined;
  ForgotPassword: undefined;
  HomeScreen: { userName?: string };
  DetailScreen: {
    item: {
      id: string;
      name: string;
      price: string;
      description: string;
      image: ImageSourcePropType;
      size: string;
    };
  };
  CartScreen: undefined;
  // Perbaiki definisi NotifikasiScreen agar menerima parameter (walaupun kosong)
  NotifikasiScreen: {
    newOrder?: {
      items: Array<{
        id: string;
        name: string;
        price: string;
        quantity: number;
      }>;
      total: number;
      status: string;
      catatan?: string;
    };
  }; 
  RiwayatScreen: undefined;
  ProfileScreen: undefined;
};

const Stack = createStackNavigator<RootStackParamList>();

const AppNavigator = () => {
  return (
    <OrderProvider>
      <Stack.Navigator screenOptions={{ headerShown: false }}>
        <Stack.Screen name="Welcome" component={WelcomeScreen} />
        <Stack.Screen name="Register" component={RegisterScreen} />
        <Stack.Screen name="Login" component={LoginScreen} />
        <Stack.Screen name="ForgotPassword" component={ForgotPasswordScreen} />
        <Stack.Screen name="HomeScreen" component={HomeScreen} />
        <Stack.Screen name="DetailScreen" component={DetailScreen} />
        <Stack.Screen name="CartScreen" component={CartScreen} />
        <Stack.Screen name="NotifikasiScreen" component={NotifikasiScreen} />
        <Stack.Screen name="RiwayatScreen" component={RiwayatScreen} />
        <Stack.Screen name="ProfileScreen" component={ProfileScreen} />
      </Stack.Navigator>
    </OrderProvider>
  );
};

export default AppNavigator;
