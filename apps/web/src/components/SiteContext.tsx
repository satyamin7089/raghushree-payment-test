import { createContext, useContext, useEffect, useState, type ReactNode } from 'react';
import { apiRequest } from '../lib/api';
import type { ProductDto, SiteConfigDto } from '../types/api';

interface SiteContextValue {
  config: SiteConfigDto | null;
  featuredProducts: ProductDto[];
  loading: boolean;
  refreshSite: () => Promise<void>;
}

const SiteContext = createContext<SiteContextValue>({
  config: null,
  featuredProducts: [],
  loading: true,
  refreshSite: async () => undefined,
});

export function SiteProvider({ children }: { children: ReactNode }) {
  const [config, setConfig] = useState<SiteConfigDto | null>(null);
  const [featuredProducts, setFeaturedProducts] = useState<ProductDto[]>([]);
  const [loading, setLoading] = useState(true);

  const refreshSite = async () => {
    try {
      const [cfg, prods] = await Promise.all([
        apiRequest<SiteConfigDto>('/api/v1/site/config'),
        apiRequest<{ items: ProductDto[] }>('/api/v1/products?limit=12'),
      ]);
      setConfig(cfg);
      setFeaturedProducts(prods.items || []);
    } catch {
      // Keep graceful defaults if offline/booting
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    void refreshSite();
  }, []);

  return (
    <SiteContext.Provider value={{ config, featuredProducts, loading, refreshSite }}>
      {children}
    </SiteContext.Provider>
  );
}

export function useSite() {
  return useContext(SiteContext);
}

export function buildWhatsappUrl(phone: string, message?: string): string {
  const digits = phone.replace(/[^0-9]/g, '');
  if (!digits) return '';
  const text = message || 'Hello Raghushree team, I would like to enquire about plastic raw materials.';
  return `https://wa.me/${digits}?text=${encodeURIComponent(text)}`;
}
