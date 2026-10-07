export type UserRole = 
  | 'admin'
  | 'staff'
  | 'technician'
  | 'accountant'
  | 'instructor'
  | 'student';

export interface User {
  id: string;
  name: string;
  email: string;
  role: UserRole;
  email_verified_at: string | null;
  created_at?: string;
  updated_at?: string;
}

export interface UserFormData {
  name: string;
  email: string;
  password?: string;
  role: UserRole | '';
}