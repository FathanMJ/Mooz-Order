import React, { useState, useEffect } from 'react';
import { View, Text, StyleSheet, SafeAreaView, TouchableOpacity, Image, TextInput, Alert, ActivityIndicator } from 'react-native';
import { Ionicons, MaterialIcons, FontAwesome } from '@expo/vector-icons';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { RootStackParamList } from '../navigation/AppNavigator';
import { profileAPI } from '../../services/api';
import * as ImagePicker from 'expo-image-picker';

type ProfileScreenNavigationProp = NativeStackNavigationProp<RootStackParamList, 'ProfileScreen'>;

interface UserData {
  id: number;
  nama: string;
  email: string;
  no_hp: string;
  alamat: string;
  profile_image?: string;
}

const ProfileScreen: React.FC = () => {
  const navigation = useNavigation<ProfileScreenNavigationProp>();
  const [userData, setUserData] = useState<UserData | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [isEditing, setIsEditing] = useState(false);
  const [editedData, setEditedData] = useState<Partial<UserData>>({});

  useEffect(() => {
    loadProfile();
  }, []);

  const loadProfile = async () => {
    try {
      setIsLoading(true);
      const response = await profileAPI.getProfile();
      if (response.status && response.data) {
        setUserData(response.data);
        setEditedData(response.data);
      }
    } catch (error: any) {
      Alert.alert('Error', error.message || 'Gagal memuat profil');
    } finally {
      setIsLoading(false);
    }
  };

  const handleGoBack = () => {
    if (navigation.canGoBack()) {
      navigation.goBack();
    } else {
      navigation.navigate('HomeScreen', { userName: userData?.nama });
    }
  };

  const handleEdit = () => {
    setIsEditing(true);
  };

  const handleSave = async () => {
    try {
      setIsLoading(true);
      const response = await profileAPI.updateProfile(editedData);
      if (response.status) {
        setUserData(prev => prev ? { ...prev, ...editedData } : null);
        setIsEditing(false);
        Alert.alert('Sukses', 'Profil berhasil diperbarui');
      }
    } catch (error: any) {
      Alert.alert('Error', error.message || 'Gagal memperbarui profil');
    } finally {
      setIsLoading(false);
    }
  };

  const handleImagePick = async () => {
    try {
      // Minta izin akses galeri
      const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
      if (status !== 'granted') {
        Alert.alert('Izin Diperlukan', 'Aplikasi membutuhkan izin untuk mengakses galeri foto Anda');
        return;
      }

      const result = await ImagePicker.launchImageLibraryAsync({
        mediaTypes: ImagePicker.MediaTypeOptions.Images,
        allowsEditing: true,
        aspect: [1, 1],
        quality: 0.5,
      });

      if (!result.canceled && result.assets[0].uri) {
        setIsLoading(true);
        const response = await profileAPI.updateProfileImage(result.assets[0].uri);
        if (response.status) {
          setUserData(prev => prev ? { ...prev, profile_image: response.data.profile_image } : null);
          Alert.alert('Sukses', 'Foto profil berhasil diperbarui');
        }
      }
    } catch (error: any) {
      Alert.alert('Error', error.message || 'Gagal memperbarui foto profil');
    } finally {
      setIsLoading(false);
    }
  };

  if (isLoading) {
    return (
      <SafeAreaView style={styles.container}>
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#FF8C00" />
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      <View style={styles.header}>
        <TouchableOpacity onPress={handleGoBack} style={styles.backButton}>
          <Ionicons name="chevron-back" size={24} color="white" />
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Profil Saya</Text>
        {!isEditing ? (
          <TouchableOpacity onPress={handleEdit} style={styles.editButton}>
            <Ionicons name="pencil" size={24} color="white" />
          </TouchableOpacity>
        ) : (
          <TouchableOpacity onPress={handleSave} style={styles.editButton}>
            <Ionicons name="checkmark" size={24} color="white" />
          </TouchableOpacity>
        )}
      </View>

      <View style={styles.profileHeader}>
        <View style={styles.profileImageContainer}>
          {userData?.profile_image ? (
            <Image 
              source={{ uri: userData.profile_image }} 
              style={styles.profileImage} 
            />
          ) : (
            <MaterialIcons name="account-circle" size={80} color="white" />
          )}
          <TouchableOpacity style={styles.cameraIconContainer} onPress={handleImagePick}>
            <Ionicons name="camera" size={20} color="#333" />
          </TouchableOpacity>
        </View>
        <Text style={styles.profileHeaderSubtitle}>Atur profil Anda</Text>
      </View>

      <View style={styles.accountInfoContainer}>
        <Text style={styles.sectionTitle}>Informasi Akun</Text>

        <View style={styles.infoItem}>
          <Text style={styles.infoLabel}>Nama</Text>
          <View style={styles.infoContent}>
            <FontAwesome name="user-o" size={18} color="#888" style={styles.infoIcon} />
            <TextInput 
              style={styles.infoText} 
              value={isEditing ? editedData.nama : userData?.nama}
              onChangeText={(text) => setEditedData(prev => ({ ...prev, nama: text }))}
              editable={isEditing}
            />
          </View>
        </View>

        <View style={styles.infoItem}>
          <Text style={styles.infoLabel}>Email</Text>
          <View style={styles.infoContent}>
            <MaterialIcons name="email" size={18} color="#888" style={styles.infoIcon} />
            <TextInput 
              style={styles.infoText} 
              value={isEditing ? editedData.email : userData?.email}
              onChangeText={(text) => setEditedData(prev => ({ ...prev, email: text }))}
              editable={isEditing}
              keyboardType="email-address"
            />
          </View>
        </View>

        <View style={styles.infoItem}>
          <Text style={styles.infoLabel}>No. HP</Text>
          <View style={styles.infoContent}>
            <Ionicons name="call-outline" size={18} color="#888" style={styles.infoIcon} />
            <TextInput 
              style={styles.infoText} 
              value={isEditing ? editedData.no_hp : userData?.no_hp}
              onChangeText={(text) => setEditedData(prev => ({ ...prev, no_hp: text }))}
              editable={isEditing}
              keyboardType="phone-pad"
            />
          </View>
        </View>

        <View style={styles.infoItem}>
          <Text style={styles.infoLabel}>Alamat</Text>
          <View style={styles.infoContent}>
            <Ionicons name="location-outline" size={18} color="#888" style={styles.infoIcon} />
            <TextInput 
              style={styles.infoText} 
              value={isEditing ? editedData.alamat : userData?.alamat}
              onChangeText={(text) => setEditedData(prev => ({ ...prev, alamat: text }))}
              editable={isEditing}
              multiline
            />
          </View>
        </View>
      </View>
    </SafeAreaView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingVertical: 15,
    paddingHorizontal: 10,
    backgroundColor: '#FF8C00',
    borderBottomLeftRadius: 20,
    borderBottomRightRadius: 20,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.2,
    shadowRadius: 4,
    elevation: 5,
  },
  backButton: {
    padding: 10,
  },
  editButton: {
    padding: 10,
  },
  headerTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: 'white',
  },
  profileHeader: {
    alignItems: 'center',
    padding: 20,
    backgroundColor: '#FF8C00',
    marginBottom: 20,
  },
  profileImageContainer: {
    width: 120,
    height: 120,
    borderRadius: 60,
    backgroundColor: '#eee',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 10,
    overflow: 'hidden',
  },
  profileImage: {
    width: 120,
    height: 120,
    borderRadius: 60,
    resizeMode: 'cover',
  },
  cameraIconContainer: {
    position: 'absolute',
    bottom: 0,
    right: 0,
    backgroundColor: 'white',
    borderRadius: 15,
    padding: 5,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.2,
    shadowRadius: 2,
    elevation: 3,
  },
  profileHeaderSubtitle: {
    fontSize: 16,
    color: 'white',
  },
  accountInfoContainer: {
    backgroundColor: 'white',
    marginHorizontal: 15,
    borderRadius: 10,
    padding: 15,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 3,
    elevation: 2,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#333',
    marginBottom: 15,
  },
  infoItem: {
    marginBottom: 15,
  },
  infoLabel: {
    fontSize: 14,
    color: '#666',
    marginBottom: 5,
  },
  infoContent: {
    flexDirection: 'row',
    alignItems: 'center',
    borderBottomWidth: 1,
    borderBottomColor: '#eee',
    paddingBottom: 8,
  },
  infoIcon: {
    marginRight: 10,
  },
  infoText: {
    fontSize: 16,
    color: '#333',
    flex: 1,
  },
});

export default ProfileScreen;