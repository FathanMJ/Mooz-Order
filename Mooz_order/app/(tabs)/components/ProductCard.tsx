import React from 'react';
import { View, Text, Image, StyleSheet, TouchableOpacity } from 'react-native';
import { useRouter } from 'expo-router';
import { useOrder } from '../context/OrderContext';
import { Ionicons } from '@expo/vector-icons';

interface ProductCardProps {
  id: number;
  name: string;
  price: number;
  image: any;
  description?: string;
}

export default function ProductCard({ 
  id, 
  name, 
  price, 
  image, 
  description,
}: ProductCardProps) {
  const router = useRouter();
  const { addOrder } = useOrder();

  const handleAddToCart = () => {
    console.log('Adding to cart:', { id, name, price, image }); 
    addOrder({
      id: id.toString(),
      name,
      price,
      image,
      quantity: 1,
      size: 'Medium',
    });
    router.push('/(tabs)/screens/CartScreen');
  };

  return (
    <TouchableOpacity 
      style={styles.card}
      onPress={() => {
        router.push({
          pathname: '/(tabs)/screens/DetailScreen',
          params: { id, name, price: price.toString(), description }
        });
      }}
    >
      <Image 
        source={image} 
        style={styles.image}
        resizeMode="cover"
      />
      
      <View style={styles.content}>
        <View style={styles.textContainer}>
          <Text style={styles.name}>{name}</Text>
          <Text style={styles.price}>Rp {price.toLocaleString()}</Text>
          {description && (
            <Text style={styles.description} numberOfLines={2}>
              {description}
            </Text>
          )}
        </View>

        <TouchableOpacity 
          style={styles.addButton}
          onPress={handleAddToCart}
        >
          <Ionicons name="add-circle" size={24} color="#FF8C00" />
        </TouchableOpacity>
      </View>
    </TouchableOpacity>
  );
}

const styles = StyleSheet.create({
  card: {
    backgroundColor: '#FFFFFF',
    borderRadius: 12,
    marginBottom: 16,
    shadowColor: '#000',
    shadowOffset: {
      width: 0,
      height: 2,
    },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
    overflow: 'hidden',
  },
  image: {
    width: '100%',
    height: 150,
    backgroundColor: '#f5f5f5',
  },
  content: {
    padding: 12,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  textContainer: {
    flex: 1,
    marginRight: 12,
  },
  name: {
    fontSize: 16,
    fontWeight: '600',
    color: '#2D2D2D',
    marginBottom: 4,
  },
  price: {
    fontSize: 14,
    fontWeight: '700',
    color: '#FF8C00',
    marginBottom: 4,
  },
  description: {
    fontSize: 12,
    color: '#666666',
  },
  addButton: {
    padding: 8,
    borderRadius: 20,
    backgroundColor: '#FFF5E6',
  },
});
