import React, { useState } from 'react';
import { View, TextInput, StyleSheet } from 'react-native';

export default function OtpInput() {
  const [otp, setOtp] = useState(['', '', '', '']);

  const handleOtpChange = (index: number, value: string) => {
    let newOtp = [...otp];
    newOtp[index] = value;
    setOtp(newOtp);
  };

  return (
    <View style={styles.otpContainer}>
      {otp.map((digit, index) => (
        <TextInput
          key={index}
          style={styles.otpInput}
          keyboardType="number-pad"
          maxLength={1}
          value={digit}
          onChangeText={(value) => handleOtpChange(index, value)}
        />
      ))}
    </View>
  );
}

const styles = StyleSheet.create({
  otpContainer: { flexDirection: 'row', justifyContent: 'center', marginVertical: 20 },
  otpInput: { backgroundColor: '#FFF', width: 50, height: 50, textAlign: 'center', fontSize: 20, borderRadius: 8, marginHorizontal: 5 }
});
