import React from 'react';
import { TextInput, StyleSheet, View, Text } from 'react-native';

interface TextInputProps {
  label?: string;
  placeholder: string;
  secureTextEntry?: boolean;
}

export default function TextInputField({ label, placeholder, secureTextEntry = false }: TextInputProps) {
  return (
    <View style={styles.container}>
      {label && <Text style={styles.label}>{label}</Text>}
      <TextInput style={styles.input} placeholder={placeholder} placeholderTextColor="gray" secureTextEntry={secureTextEntry} />
    </View>
  );
}

const styles = StyleSheet.create({
  container: { width: '80%', marginVertical: 10 },
  label: { fontSize: 14, color: '#FFF', marginBottom: 5 },
  input: { backgroundColor: '#FFF', padding: 10, borderRadius: 8 }
});
