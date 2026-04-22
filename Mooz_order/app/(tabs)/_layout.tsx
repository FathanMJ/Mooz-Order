import { Tabs } from 'expo-router';
import { OrderProvider } from './context/OrderContext';

export default function TabLayout() {
  return (
    <OrderProvider>
      <Tabs
        screenOptions={{
          headerShown: false,
          tabBarStyle: {
            display: 'none', // 👈 Menyembunyikan navbar
          },
        }}
      >
        <Tabs.Screen name="index" />
        <Tabs.Screen name="explore" />
        <Tabs.Screen name="cart" />
        <Tabs.Screen name="ProfileScreen" />
      </Tabs>
    </OrderProvider>
  );
}
