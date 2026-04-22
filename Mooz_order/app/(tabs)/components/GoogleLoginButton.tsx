import React from 'react';
import { TouchableOpacity, Text, StyleSheet, Image } from 'react-native';

export default function GoogleLoginButton() {
  return (
    <TouchableOpacity style={styles.googleButton}>
      <Image source={require('../assets/images/google.png')} style={styles.icon} />
      <Text style={styles.googleButtonText}>Login dengan Google</Text>
    </TouchableOpacity>
  );
}

const styles = StyleSheet.create({
  googleButton: { flexDirection: 'row', backgroundColor: '#FFF', padding: 10, borderRadius: 8, width: '80%', alignItems: 'center', justifyContent: 'center', marginVertical: 10 },
  googleButtonText: { fontSize: 16, color: '#FF8C00', marginLeft: 10 },
  icon: { width: 20, height: 20 }
});
