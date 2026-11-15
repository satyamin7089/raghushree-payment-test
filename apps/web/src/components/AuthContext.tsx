import { createContext, useContext, useEffect, useState, type ReactNode } from 'react';
import { apiRequest, getAuthToken, setAuthToken } from '../lib/api';
import type { AuthUserDto } from '../types/api';

interface AuthContextValue {
  user: AuthUserDto | null;
  loading: boolean;
  login: (email: string, password: string) => Promise<AuthUserDto>;
  logout: () => Promise<void>;
  hasPermission: (perm: string) => boolean;
}

const AuthContext = createContext<AuthContextValue | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<AuthUserDto | null>(null);
  const [loading, setLoading] = useState(Boolean(getAuthToken()));

  useEffect(() => {
    const token = getAuthToken();
    if (!token) {
      setLoading(false);
      return;
    }
    apiRequest<{ user: AuthUserDto }>('/api/v1/auth/me')
      .then((res) => setUser(res.user))
      .catch(() => {
        setAuthToken(null);
        setUser(null);
      })
      .finally(() => setLoading(false));
  }, []);

  const login = async (email: string, password: string) => {
    const res = await apiRequest<{ token: string; user: AuthUserDto }>('/api/v1/auth/login', {
      method: 'POST',
      body: JSON.stringify({ email, password }),
    });
    setAuthToken(res.token);
    setUser(res.user);
    return res.user;
  };

  const logout = async () => {
    try {
      await apiRequest('/api/v1/auth/logout', { method: 'POST' });
    } catch {
      // Ignore network errors on logout
    }
    setAuthToken(null);
    setUser(null);
  };

  const hasPermission = (perm: string) => {
    if (!user) return false;
    if (user.role === 'administrator' || user.permissions.includes('*')) return true;
    return user.permissions.includes(perm);
  };

  return (
    <AuthContext.Provider value={{ user, loading, login, logout, hasPermission }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth(): AuthContextValue {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth must be used within AuthProvider');
  return ctx;
}
