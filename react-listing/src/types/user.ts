export interface Geo {
  lat: string;
  lng: string;
}

export interface Address {
  street: string;
  suite: string;
  city: string;
  zipcode: string;
  geo: Geo;
}

export interface Company {
  name: string;
  catchPhrase: string;
  bs: string;
}

export interface User {
  id: number;
  name: string;
  username: string;
  email: string;
  address: Address;
  phone: string;
  website: string;
  company: Company;
}

export type UsersResponse = User[];

export interface UsersState {
  users: User[];
  isLoading: boolean;
  error: string | null;
}

export interface UserCardProps {
  user: User;
  onClick: (user: User) => void;
}

export interface UserListProps {
  users: User[];
  isLoading: boolean;
  error: string | null;
  onUserClick: (user: User) => void;
  onRetry: () => void;
}

export interface UserSearchProps {
  value: string;
  onChange: (value: string) => void;
  placeholder?: string;
  isSearching?: boolean;
}

export interface UserModalProps {
  user: User | null;
  isOpen: boolean;
  onClose: () => void;
}

export interface ModalProps {
  isOpen: boolean;
  onClose: () => void;
  title?: string;
  children: React.ReactNode;
}

export interface ErrorMessageProps {
  message: string;
  onRetry?: () => void;
}

export interface EmptyStateProps {
  message?: string;
}
