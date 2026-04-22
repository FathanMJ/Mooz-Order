import { StyleSheet } from 'react-native';
import { ThemedView } from '@/components/ThemedView';
import AppNavigator from './navigation/AppNavigator';

export default function IndexScreen() {
  return (
    <ThemedView style={styles.container}>
      <AppNavigator />
    </ThemedView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
  },
});
