import { useState, useCallback } from 'react';
import { User } from '../types/user';

interface UseModalReturn {
  isOpen: boolean;
  selectedUser: User | null;
  openModal: (user: User) => void;
  closeModal: () => void;
}

export const useModal = (): UseModalReturn => {
  const [isOpen, setIsOpen] = useState(false);
  const [selectedUser, setSelectedUser] = useState<User | null>(null);

  const openModal = useCallback((user: User) => {
    setSelectedUser(user);
    setIsOpen(true);
  }, []);

  const closeModal = useCallback(() => {
    setIsOpen(false);
    setTimeout(() => setSelectedUser(null), 200);
  }, []);

  return { isOpen, selectedUser, openModal, closeModal };
};
